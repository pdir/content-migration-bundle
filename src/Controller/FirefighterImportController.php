<?php

declare(strict_types=1);

/*
 * Content migration bundle for Contao Open Source CMS
 *
 * Copyright (c) 2022 pdir / digital agentur // pdir GmbH
 *
 * @package    content-migration-bundle
 * @link       https://pdir.de
 * @license    LGPL-3.0+
 * @author     Mathias Arzberger <develop@pdir.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pdir\ContentMigrationBundle\Controller;

use Ausi\SlugGenerator\SlugGenerator;
use Ausi\SlugGenerator\SlugOptions;
use con4gis\FirefighterBundle\Resources\contao\models\C4gFirefighterOperationCategoriesModel;
use con4gis\FirefighterBundle\Resources\contao\models\C4gFirefighterOperationsModel;
use con4gis\FirefighterBundle\Resources\contao\models\C4gFirefighterUnitsModel;
use Contao\BackendTemplate;
use Contao\CoreBundle\Filesystem\FilesystemUtil;
use Contao\CoreBundle\Filesystem\FilesystemItem;
use Contao\CoreBundle\Filesystem\FilesystemItemIterator;
use Contao\CoreBundle\Filesystem\VirtualFilesystem;
use Contao\ContentModel;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Dbafs;
use Contao\Environment;
use Contao\FilesModel;
use Contao\Message;
use Contao\NewsArchiveModel;
use Contao\NewsModel;
use Contao\System;
use Contao\UserModel;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Pdir\ContentMigrationBundle\Exporter\ModelExporter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class FirefighterImportController
{
    /**
     * @var int;
     */
    public int $itemCounter = 0;

    /**
     * @var int;
     */
    public int $imageCounter = 0;

    /**
     * @var string;
     */
    public string $imagePath;

    /**
     * @var string;
     */
    public string $typo3domain;

    private ContaoFramework $framework;

    private RequestStack $requestStack;

    /**
     * @var Connection;
     */
    private Connection $remoteConn;

    /**
     * ExportController constructor.
     */
    public function __construct(ContaoFramework $framework, RequestStack $requestStack, private VirtualFilesystem $filesStorage)
    {
        $this->framework = $framework;
        $this->requestStack = $requestStack;
    }

    /**
     * Run the controller.
     *
     * @codeCoverageIgnore
     *
     * @throws \Exception
     */
    public function run(): string
    {
        $formId = 'tl_c4g_firefighter_operations_import';

        $request = $this->requestStack->getCurrentRequest();

        if ($request->request->get('FORM_SUBMIT') === $formId) {
            $this->processForm($request);
        }

        return $this->getTemplate($formId)->parse();
    }

    /**
     * Process the form.
     *
     * @codeCoverageIgnore
     */
    protected function processForm(Request $request): void
    {
        // set news params
        $this->author = $request->get('author');
        $this->imagePath = $request->get('image_folder');
        $this->databasePrefix = $request->get('remote_database_prefix');

        // set db params for remote
        $params = [
            'dbname' => $request->get('remote_database'),
            'user' => $request->get('remote_user'),
            'password' => $request->get('remote_password'),
            'host' => $request->get('remote_host'),
            'driver' => 'pdo_mysql',
            'charset' => 'utf8mb4',
        ];

        // test connection to typo3 database
        try {
            $config = new Configuration();
            $this->remoteConn = DriverManager::getConnection($params, $config);
        } catch (ImportException $e) {
            /** @var Message $message */
            $message = $this->framework->getAdapter(Message::class);
            $message->addError($e->getMessage());
        }

        // Import units from eiko_organisationen to tl_c4g_firefighter_units
        try {
            $sql = 'SELECT * FROM '.$this->databasePrefix.'eiko_organisationen';
            $stmt = $this->remoteConn->query($sql);

            while (($row = $stmt->fetchAssociative()) !== false) {
                // $this->addUnit($row);
            }

        } catch (ImportException $e) {
            /** @var Message $message */
            $message = $this->framework->getAdapter(Message::class);
            $message->addError($e->getMessage());
        }

        // Import categories from eiko_tickerkat to tl_c4g_firefighter_operation_categories
        try {
            // run db import with database prefix
            $sql = 'SELECT * FROM '.$this->databasePrefix.'eiko_tickerkat';
            $stmt = $this->remoteConn->query($sql);
            // $stmt->execute();

            while (($row = $stmt->fetchAssociative()) !== false) {
                // $this->addCategory($row);
            }

            $message = $this->framework->getAdapter(Message::class);
            $message->addConfirmation(sprintf($GLOBALS['TL_LANG']['tl_c4g_firefighter_operations']['import_message']['complete'], $this->itemCounter, $this->imageCounter));
        } catch (ImportException $e) {
            /** @var Message $message */
            $message = $this->framework->getAdapter(Message::class);
            $message->addError($e->getMessage());
        }

        // Import operations from eiko_einsatzberichte to tl_c4g_firefighter_operations
        try {
            // run db import with database prefix
            $sql = 'SELECT * FROM '.$this->databasePrefix.'eiko_einsatzberichte'; #' WHERE id = 300;';
            $stmt = $this->remoteConn->query($sql);
            // $stmt->execute();

            while (($row = $stmt->fetchAssociative()) !== false) {
                $this->addItem($row);
            }

            $message = $this->framework->getAdapter(Message::class);
            $message->addConfirmation(sprintf($GLOBALS['TL_LANG']['tl_c4g_firefighter_operations']['import_message']['complete'], $this->itemCounter, $this->imageCounter));
        } catch (ImportException $e) {
            /** @var Message $message */
            $message = $this->framework->getAdapter(Message::class);
            $message->addError($e->getMessage());
        }

        /** @var Controller $controller */
        $controller = $this->framework->getAdapter(Controller::class);
        $controller->reload();
    }

    /**
     * Get the template.
     *
     * @codeCoverageIgnore
     *
     * @throws \Exception
     */
    protected function getTemplate(string $formId): BackendTemplate
    {
        /**
         * @var Environment
         * @var Message     $message
         * @var System      $system
         */
        $environment = $this->framework->getAdapter(Environment::class);
        $message = $this->framework->getAdapter(Message::class);
        $system = $this->framework->getAdapter(System::class);

        $GLOBALS['TL_CSS'][] = 'bundles/pdircontentmigration/css/backend.css';

        $template = new BackendTemplate('be_firefighter_import');
        $template->currentUserFolder = ModelExporter::getCurrentUserFolder();
        $template->backUrl = $system->getReferer();
        $template->action = $environment->get('request');
        $template->formId = $formId;
        $template->typeOptions = $this->generateTypeOptions();
        $template->newsArchiveOptions = $this->generateNewsArchiveOptions();
        $template->newsAuthorOptions = $this->generateNewsAuthorOptions();
        $template->message = $message->generate();

        return $template;
    }

    /**
     * Generate the import type options.
     *
     * @codeCoverageIgnore
     */
    protected function generateTypeOptions(): array
    {
        $options = [];

        foreach ($GLOBALS['TL_LANG']['tl_c4g_firefighter_operations']['import_typeRef'] as $alias => &$label) {
            $options[$alias] = $GLOBALS['TL_LANG']['tl_c4g_firefighter_operations']['import_typeRef'][$alias];
        }
        unset($label);

        return $options;
    }

    /**
     * Generate the news archive options.
     *
     * @codeCoverageIgnore
     */
    protected function generateNewsArchiveOptions(): array
    {
        $objArchive = NewsArchiveModel::findAll();

        $return = [];

        if (null !== $objArchive) {
            while ($objArchive->next()) {
                $return[$objArchive->id] = $objArchive->title;
            }
        }

        return $return;
    }

    /**
     * Generate the news archive options.
     *
     * @codeCoverageIgnore
     */
    protected function generateNewsAuthorOptions(): array
    {
        $objUser = UserModel::findAll();

        $return = [];

        if (null !== $objUser) {
            while ($objUser->next()) {
                $return[$objUser->id] = $objUser->name;
            }
        }

        return $return;
    }

    /**
     * Add unit
     */
    protected function addUnit(array $data): void
    {
        $model = new C4gFirefighterUnitsModel();

        // add fields
        $model->id = $data['id'];
        $model->tstamp = time();
        $model->importId = 1;
        $model->caption = $data['name'];
        $model->unit_type_id = (1 === (int)$data['ffw'])? 1 : 0;
        $model->link = $data['link'];
        $model->geox = $data['gmap_latitude']?? '';
        $model->geoy = $data['gmap_longitude']?? '';
        $model->detail = $data['detail'];
        $model->icon_orga = $this->getImageFromPath($data['gmap_icon_orga']);
        $model->icon = $this->getImageFromPath($data['gmap_icon']);

        $model->save();
    }

    /**
     * Add category
     */
    protected function addCategory(array $data): void
    {
        $model = new C4gFirefighterOperationCategoriesModel();

        // add fields
        $model->id = $data['id'];
        $model->tstamp = time();
        $model->importId = 1;
        $model->operation_type = 1;
        $model->operation_category = $data['title'];
        $model->operation_locstyles = 2;
        $model->singleSRC = $this->getImageFromPath($data['image']);
        $model->desc = $data['beschreibung'];

        $model->save();
    }

    /**
     * Add single item to operations.
     *
     * @throws \Exception
     * @codeCoverageIgnore
     */
    protected function addItem(array $data): void
    {
        $model = new C4gFirefighterOperationsModel();

        // add fields
        $model->tstamp = \strtotime($data['createdate']);
        $model->importId = 1;
        $model->caption = $this->replaceSpecialCharacters($data['summary']??'');
        $model->description = $data['desc'];
        $model->operation_type = 1;
        $model->operation_category = $data['tickerkat'];
        $model->operation_leader =
        $model->addTime = 1;
        $model->startTime = \strtotime($data['date1']);
        $model->endTime = ('0000-00-00 00:00:00' !== $data['date3'])? \strtotime($data['date3']) : \strtotime($data['date1']);
        $model->startDate = \strtotime($data['date1']);
        $model->endDate = ('0000-00-00 00:00:00' !== $data['date3'])? \strtotime($data['date3']) : \strtotime($data['date1']);;
        $model->location = $data['address'];
        $model->loc_geox = $data['gmap_report_latitude']?? '';
        $model->loc_geoy = $data['gmap_report_longitude']?? '';
        $model->vehicles = \serialize(\explode(',', $data['vehicles']));
        $model->units = \serialize(\explode(',', $data['auswahl_orga']));
        $model->numberStaff = 0;
        $model->pressRelease1 = $data['presse'];
        $model->pressRelease2 = $data['presse2'];
        $model->pressRelease3 = $data['presse3'];
        $model->published = (1 === (int)$data['state']) ? 1 : 0;
        $model->userId = $this->author;
        $model->counter = $data['counter'];

        // add all images to gallery if exists
        if ('' !== $data['image']) {
            # dump($data['image']);
            //die();
        }
        // $model->gallery = ('' !== $data['image'])? \serialize([$this->getImageFromPath($data['image'])]) : '';

        $gallery = [];
        if ('' !== $data['image']) {
            $gallery[] = $this->getImageFromPath($data['image']);
            ++$this->imageCounter;
        }

        /*if($data['assets_id']) {
            $jImages = \explode(',', $data['image']);
            foreach($jImages as $img) {

            }

            ++$this->imageCounter;
        } */

        $gallery = array_merge($gallery, $this->getImagesFromFolder('/einsatzbilder/'.$data['id']));

        $model->gallery = \serialize($gallery);

        # dump($data);
        #dump($model->gallery);
        #die();
        #if(10 === $this->imageCounter) {
        #    die();
        #}
        //die();
        $model->save();

        ++$this->itemCounter;
    }

    private function getImageFromPath($path): string
    {
        if ('' === $path) {
            return '';
        }

        // Fix path
        $path = \str_replace(['images/images/com_einsatzkomponente','images/com_einsatzkomponente'], '', $path);

        $file = $this->imagePath.$path;
        $filesModel = FilesModel::findByPath($file);

        // check if files model and file exists
        if (null === $filesModel && \file_exists(TL_ROOT.'/'.$file)) {
            // get the file object to retrieve UUID
            $filesModel = Dbafs::addResource($file);
        }

        return $filesModel->uuid?? '';
    }

    private function getImagesFromFolder($folder): array
    {
        $uuids = [];
        $path = $this->imagePath.$folder;

        $pathModel = FilesModel::findByPath($path);

        if (null === $pathModel) {
            return $uuids;
        }

        $files = FilesModel::findByPid($pathModel->uuid, ['order' => 'name']);

        if (null === $files) {
            return $uuids;
        }

        #dump($files);
        foreach($files as $file) {
            $uuids[] = $file->uuid;
        }

        return $uuids;
    }

    /**
     * Get slug for alias.
     *
     * @param $str
     */
    private function getSlug($str): string
    {
        // use slug generator
        $generator = new SlugGenerator(
            (new SlugOptions())
                ->setValidChars('a-z0-9')
                ->setLocale('de')
                ->setDelimiter('-')
        );

        return $generator->generate($str);
    }

    /**
     * Get Text from typo3 bodytext.
     *
     * @codeCoverageIgnore
     */
    private function getText(string $str): string
    {
        $pattern = '/(<link ([^>]*)>)(.*?)(<\\/link>)/si';

        preg_match_all($pattern, $str, $regs);

        for ($i = 0; $i < \count($regs[0]); ++$i) {
            if (is_numeric($regs[2][$i])) {
                // Internal link
                $str = str_replace($regs[1][$i], '<a href="http://'.$this->typo3domain.'/index.php?id='.$regs[2][$i].'">', $str);
            } elseif (false !== strpos($regs[2][$i], '@')) {
                // Email address
                $str = str_replace($regs[1][$i], '<a href="mailto:'.$regs[2][$i].'">', $str);
            } else {
                // External links
                $link = explode(' ', $regs[2][$i]);
                $str = str_replace($regs[1][$i], '<a href="'.$link[0].'" target="'.$link[1].'">', $str);
            }
        }

        return str_replace('</link>', '</a>', $str);
    }

    public function replaceSpecialCharacters($str): string
    {
        if (null === $str) {
            return '';
        }

        return str_replace(['&#40;', '&#41;'], ['(', ')'], $str);
    }
}
