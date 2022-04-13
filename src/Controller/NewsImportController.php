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
use Contao\BackendTemplate;
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

class NewsImportController
{
    /**
     * @var int;
     */
    public int $newsCounter = 0;

    /**
     * @var int;
     */
    public int $imageCounter = 0;

    /**
     * @var int;
     */
    public int $newsArchive;

    /**
     * @var int;
     */
    public int $newsAuthor;

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
    private Connection $typo3conn;

    /**
     * ExportController constructor.
     */
    public function __construct(ContaoFramework $framework, RequestStack $requestStack)
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
        $formId = 'tl_news_archive_import';

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
        $this->newsArchive = $request->get('typo3_tt_news_contao_news_archive');
        $this->newsAuthor = $request->get('typo3_tt_news_contao_news_author');
        $this->imagePath = $request->get('typo3_tt_news_image_folder');
        $this->typo3domain = $request->get('typo3_tt_news_domain');

        // set db params for remote
        $params = [
            'dbname' => $request->get('typo3_tt_news_database'),
            'user' => $request->get('typo3_tt_news_user'),
            'password' => $request->get('typo3_tt_news_password'),
            'host' => $request->get('typo3_tt_news_host'),
            'driver' => 'pdo_mysql',
            'charset' => 'utf8mb4',
        ];

        // test connection to typo3 database
        try {
            $config = new Configuration();
            $this->typo3conn = DriverManager::getConnection($params, $config);
        } catch (ImportException $e) {
            /** @var Message $message */
            $message = $this->framework->getAdapter(Message::class);
            $message->addError($e->getMessage());
        }

        try {
            // run db import
            $sql = 'SELECT * FROM tt_news WHERE pid = ? AND sys_language_uid = ?';
            $stmt = $this->typo3conn->prepare($sql);
            $stmt->execute([$request->get('typo3_tt_news_pid'), $request->get('typo3_tt_news_lng')])
            ;

            while (($row = $stmt->fetchAssociative()) !== false) {
                $this->addNews($row);
            }

            $message = $this->framework->getAdapter(Message::class);
            $message->addConfirmation(sprintf($GLOBALS['TL_LANG']['tl_news_archive']['import_message']['complete'], $this->newsCounter, $this->imageCounter));
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

        $template = new BackendTemplate('be_news_import');
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

        foreach ($GLOBALS['TL_LANG']['tl_news_archive']['import_typeRef'] as $alias => &$label) {
            $options[$alias] = $GLOBALS['TL_LANG']['tl_news_archive']['import_typeRef'][$alias];
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
     * Add singl news to existing news archive.
     *
     * @throws \Exception
     * @codeCoverageIgnore
     */
    protected function addNews(array $data): void
    {
        $newsModel = new NewsModel();

        // add fields
        $newsModel->pid = $this->newsArchive;
        $newsModel->author = $this->newsAuthor;
        $newsModel->alias = $this->getSlug($data['title']);
        $newsModel->tstamp = $data['tstamp'];
        $newsModel->date = $data['datetime'];
        $newsModel->time = $data['datetime'];
        $newsModel->start = $data['starttime'];
        $newsModel->end = $data['endtime'];

        $newsModel->published = 0 === $data['hidden'] ? 1 : 0;
        $newsModel->headline = $data['title'];
        $newsModel->subheadline = $data['short'];

        // add teaser
        $teaser = strip_tags($data['bodytext']);
        $newsModel->teaser = substr($teaser, 0, strpos($teaser, '.') + 1);

        // ádd meta
        $newsModel->pageTitle = $data['title'];
        $newsModel->description = $newsModel->teaser;

        // add deleted notice and set unpublished
        if ($data['deleted']) {
            $newsModel->headline .= ' [deleted]';
            $newsModel->published = 0;
        }

        // add first image if exists
        if ($data['image'] && '' !== $data['image']) {
            $images = explode(',', $data['image']);
            $captions = explode(\chr(10), $data['imagecaption']);
            $alts = explode(\chr(10), $data['imagealttext']);
            $titles = explode(\chr(10), $data['imagetitletext']);
            $links = explode(\chr(10), $data['links']);

            if (\is_array($images)) {
                $file = $this->imagePath.$images[0];
                $filesModel = FilesModel::findByPath($file);

                // check if files model and file exists
                if (null === $filesModel && file_exists(TL_ROOT.'/'.$file)) {
                    // get the file object to retrieve UUID
                    $filesModel = Dbafs::addResource($file);
                }

                if (null !== $filesModel) {
                    $newsModel->addImage = 1;
                    $newsModel->singleSRC = $filesModel->uuid;
                    $newsModel->alt = $alts[0];
                }
            }
        }

        $newsModel->save();

        // add full description to tl_content
        $contentModel = new ContentModel();
        $contentModel->type = 'text';
        $contentModel->ptable = 'tl_news';
        $contentModel->pid = $newsModel->id;
        $contentModel->sorting = 128;
        $contentModel->text = $this->getText($data['bodytext']);
        $contentModel->save();

        // add all images to tl_content
        if ($data['image']) {
            for ($i = 0; $i < \count($images); ++$i) {
                $filesModel = FilesModel::findByPath($this->imagePath.$images[$i]);

                // check if files model and file exists
                if (null === $filesModel && file_exists(TL_ROOT.'/'.$file)) {
                    // get the file object to retrieve UUID
                    $filesModel = Dbafs::addResource($file);
                }

                if (null !== $filesModel) {
                    $contentModel = new ContentModel();
                    $contentModel->type = 'image';
                    $contentModel->ptable = 'tl_news';
                    $contentModel->pid = $newsModel->id;
                    $contentModel->sorting = 256;
                    $contentModel->singleSRC = $filesModel->uuid;
                    $contentModel->save();

                    // save caption, alt and links to image
                    if ('' === $filesModel->meta) {
                        $filesModel->meta = serialize([
                            'title' => $titles[$i],
                            'alt' => $alts[$i],
                            'link' => $links[$i],
                            'caption' => $captions[$i],
                        ]);
                        $filesModel->save();
                    }

                    ++$this->imageCounter;
                }
            }
        }

        ++$this->newsCounter;
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
}
