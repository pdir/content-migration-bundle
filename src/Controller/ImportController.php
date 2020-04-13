<?php

/*
 * Content migration bundle for Contao Open Source CMS
 *
 * Copyright (c) 2020 pdir / digital agentur // pdir GmbH
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

use Contao\ArticleModel;
use Contao\BackendTemplate;
use Contao\ContentModel;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Environment;
use Contao\File;
use Contao\FilesModel;
use Contao\Message;
use Contao\PageModel;
use Contao\System;
use Pdir\ContentMigrationBundle\Exporter\ModelExporter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ImportController
{
    /**
     * @var ContaoFramework
     */
    private $framework;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var array $replacementIds
     */
    private $replacementIds;

    /**
     * ExportController constructor.
     *
     * @param ContaoFramework $framework
     * @param RequestStack $requestStack
     */
    public function __construct(
        ContaoFramework $framework,
        RequestStack $requestStack
    ) {
        $this->framework = $framework;
        $this->requestStack = $requestStack;
    }

    /**
     * Run the controller.
     *
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function run()
    {
        $formId = 'tl_page_import';

        $request = $this->requestStack->getCurrentRequest();

        if ($request->request->get('FORM_SUBMIT') === $formId) {
            $this->processForm($request);
        }

        return $this->getTemplate($formId)->parse();
    }

    /**
     * Process the form.
     *
     * @param Request $request
     *
     * @codeCoverageIgnore
     */
    protected function processForm(Request $request)
    {
        $userFolder = $request->get('import_name');

        try {
            // load data from files
            $filesModel = $this->getModelFiles($userFolder);

            // run imports
            $pageCounter = $this->importPages($filesModel);
            $articleCounter = $this->importArticles($filesModel);
            $contentCounter = $this->importContent($filesModel);

            $message = $this->framework->getAdapter(Message::class);
            $message->addConfirmation(sprintf($GLOBALS['TL_LANG']['tl_page']['import_message']['complete'], $pageCounter, $articleCounter, $contentCounter, reset($this->replacementIds)));

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
     * @param string $formId
     *
     * @return BackendTemplate
     *
     * @codeCoverageIgnore
     */
    protected function getTemplate($formId)
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

        $template = new BackendTemplate('be_page_import');
        $template->currentUserFolder = ModelExporter::getCurrentUserFolder();
        $template->backUrl = $system->getReferer();
        $template->action = $environment->get('request');
        $template->formId = $formId;
        $template->folderOptions = $this->generateFolderOptions();
        $template->typeOptions = $this->generateTypeOptions();
        $template->message = $message->generate();

        return $template;
    }

    /**
     * Generate the import type options.
     *
     * @return array
     *
     * @codeCoverageIgnore
     */
    protected function generateTypeOptions()
    {
        $options = [];

        foreach ($GLOBALS['TL_LANG']['tl_page']['import_typeRef'] as $alias => $label) {
            $options[$alias] = $GLOBALS['TL_LANG']['tl_page']['import_typeRef'][$alias];
        }

        return $options;
    }

    /**
     * Generate the options.
     *
     * @return array
     *
     * @codeCoverageIgnore
     */
    protected function generateFolderOptions()
    {
        $objUserDir = FilesModel::findByPath(ModelExporter::getCurrentUserFolder());
        $objSubfiles = FilesModel::findByPid($objUserDir->uuid, array('order' => 'name'));

        $options = [];

        if ($objSubfiles === null ) {
            return [];
        }

        foreach ($objSubfiles as $file) {
            // use only subfolders
            if($file->type === 'folder')
            {
                $options[$file->path] = $file->name;
            }
        }

        return $options;
    }

    protected function getModelFiles($folder)
    {
        /* @var FilesModel $filesModel */
        $filesModel = FilesModel::findByPath($folder);
        /* @var FilesModel $objSubfiles */
        $objSubfiles = FilesModel::findByPid($filesModel->uuid, array('order' => 'name'));

        if ($objSubfiles === null ) {
            return null;
        }

        return $objSubfiles;
    }

    protected function importPages($filesModel) {
        $pageCounter = 0;
        foreach ($filesModel as $fileModel) {

            if($fileModel->type === 'file')
            {
                if(strpos($fileModel->name,"page") === 0)
                {
                    $modelArr = $this->getModelFileContent($fileModel->path);

                    $pageModel = new PageModel();

                    if(!$modelArr['title'])
                    {
                        continue;
                    }

                    foreach($modelArr as $key => $value)
                    {
                        // prevent setting id
                        if($key == 'id')
                        {
                            $lastId = $value;
                            continue;
                        }
                        if($key == 'pid' && $pageCounter > 0)
                        {
                            $pageModel->pid = $this->replacementIds['pagePid' . $value];
                            continue;
                        }

                        $pageModel->{$key} = $value;
                    }

                    // write model to db
                    $pageModel->save();

                    $this->replacementIds['pagePid' . $lastId] = $pageModel->id;
                    $pageCounter++;
                }
            }
        }

        return $pageCounter;
    }

    protected function importArticles($filesModel) {
        $articleCounter = 0;

        foreach ($filesModel as $fileModel)
        {
            if ($fileModel->type === 'file')
            {
                if(strpos($fileModel->name,"article") === 0)
                {
                    $modelArr = $this->getModelFileContent($fileModel->path);
                    $articleModel = new ArticleModel();

                    foreach($modelArr as $key => $value)
                    {
                        // prevent setting id
                        if($key == 'id')
                        {
                            $lastId = $value;
                            continue;
                        }
                        if($key == 'pid')
                        {
                            $articleModel->pid = $this->replacementIds['pagePid' . $value];
                            continue;
                        }

                        $articleModel->{$key} = $value;
                    }
                    $articleModel->save();
                    $this->replacementIds['articlePid' . $lastId] = $articleModel->id;
                    $articleCounter++;
                }
            }
        }

        return $articleCounter;
    }

    protected function importContent($filesModel) {
        $contentCounter = 0;

        foreach ($filesModel as $fileModel)
        {
            if ($fileModel->type === 'file')
            {
                if(strpos($fileModel->name,"content") === 0)
                {
                    $modelArr = $this->getModelFileContent($fileModel->path);

                    $contentModel = new ContentModel();

                    foreach($modelArr as $key => $value)
                    {
                        // prevent setting id
                        if($key == 'id')
                        {
                            $lastId = $value;
                            continue;
                        }
                        if($key == 'pid')
                        {
                            $contentModel->pid = $this->replacementIds['articlePid' . $value];
                            continue;
                        }

                        $contentModel->{$key} = $value;
                    }

                    $contentModel->save();
                    $contentCounter++;
                }
            }
        }

        return $contentCounter;
    }

    protected function getModelFileContent($path)
    {
        $file = new File($path);
        return unserialize($file->getContent());
    }
}
