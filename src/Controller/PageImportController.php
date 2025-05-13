<?php

declare(strict_types=1);

/*
 * Content migration bundle for Contao Open Source CMS
 *
 * Copyright (c) 2025 pdir / digital agentur // pdir GmbH
 *
 * @package    content-migration-bundle
 * @link       https://pdir.de
 * @license    LGPL-3.0+
 * @author     pdir GmbH <https://pdir.de>
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
use Contao\Dbafs;
use Contao\Environment;
use Contao\File;
use Contao\FilesModel;
use Contao\Message;
use Contao\PageModel;
use Contao\System;
use Model\Collection;
use Pdir\ContentMigrationBundle\Exporter\ModelExporter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class PageImportController
{
    private ContaoFramework $framework;

    private RequestStack $requestStack;

    private array $replacementIds;

    private array $pageFields = [];

    private array $articleFields = [];

    private array $contentFields = [];

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
     */
    public function run(): string
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
     * @codeCoverageIgnore
     */
    protected function processForm(Request $request): void
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     */
    protected function generateTypeOptions(): array
    {
        $options = [];

        foreach ($GLOBALS['TL_LANG']['tl_page']['import_typeRef'] as $alias => &$label) {
            $options[$alias] = $GLOBALS['TL_LANG']['tl_page']['import_typeRef'][$alias];
        }
        unset($label);

        return $options;
    }

    /**
     * Generate the options.
     *
     * @codeCoverageIgnore
     *
     * @throws \Exception
     */
    protected function generateFolderOptions(): array
    {
        // Sync filesystem to get freshly uploaded folders also
        Dbafs::syncFiles();

        $objUserDir = FilesModel::findByPath(ModelExporter::getCurrentUserFolder());
        $objSubfiles = FilesModel::findByPid($objUserDir->uuid, ['order' => 'name']);

        $options = [];

        if (null === $objSubfiles) {
            return [];
        }

        foreach ($objSubfiles as $file) {
            // use only sub folders
            if ('folder' === $file->type) {
                $options[$file->path] = $file->name;
            }
        }

        return $options;
    }

    /**
     * @return FilesModel|Collection|null
     */
    protected function getModelFiles(string $folder)
    {
        /** @var FilesModel $filesModel */
        $filesModel = FilesModel::findByPath($folder);

        /** @var FilesModel $objSubfiles */
        $objSubfiles = FilesModel::findByPid($filesModel->uuid, ['order' => 'name']);

        if (null === $objSubfiles) {
            return null;
        }

        return $objSubfiles;
    }

    /**
     * @throws \Exception
     */
    protected function importPages($filesModel): int
    {
        $pageCounter = 0;

        // Set pageFields
        foreach ($GLOBALS['TL_DCA']['tl_page']['fields'] as $key => $field) {
            if (is_array($field['sql']) && 'boolean' === $field['sql']['type']) {
                $this->pageFields[] = $key;
            }
        }

        foreach ($filesModel as $fileModel) {
            if ('file' === $fileModel->type) {
                if (str_starts_with($fileModel->name, 'page')) {
                    $modelArr = $this->getModelFileContent($fileModel->path);

                    $pageModel = new PageModel();

                    if (!$modelArr['title']) {
                        continue;
                    }

                    $lastId = null;

                    foreach ($modelArr as $key => $value) {
                        // prevent setting id
                        if ('id' === $key) {
                            $lastId = $value;
                            continue;
                        }

                        if ('pid' === $key && $pageCounter > 0) {
                            // $lastPid = $key;
                            if (!$this->replacementIds['pagePid'.$value]) {
                                $this->replacementIds['pagePid'.$value] = $value;
                            }

                            $pageModel->pid = $this->replacementIds['pagePid'.$value];
                            continue;
                        }

                        $pageModel->{$key} = $value;
                    }

                    // Fix incorrect integer values for older export files
                    $this->correctData($this->pageFields, $pageModel);

                    // write model to db
                    $pageModel->save();

                    $this->replacementIds['pagePid'.$lastId] = $pageModel->id;
                    ++$pageCounter;
                }
            }
        }

        return $pageCounter;
    }

    /**
     * @throws \Exception
     */
    protected function importArticles($filesModel): int
    {
        $articleCounter = 0;

        // Set articleFields
        foreach ($GLOBALS['TL_DCA']['tl_article']['fields'] as $key => $field) {
            if (is_array($field['sql']) && 'boolean' === $field['sql']['type']) {
                $this->articleFields[] = $key;
            }
        }

        foreach ($filesModel as $fileModel) {
            if ('file' === $fileModel->type) {
                if (str_starts_with($fileModel->name, 'article')) {
                    $modelArr = $this->getModelFileContent($fileModel->path);
                    $articleModel = new ArticleModel();

                    $lastId = null;

                    foreach ($modelArr as $key => $value) {
                        // prevent setting id
                        if ('id' === $key) {
                            $lastId = $value;
                            continue;
                        }

                        if ('pid' === $key) {
                            $articleModel->pid = $this->replacementIds['pagePid'.$value];
                            continue;
                        }

                        $articleModel->{$key} = $value;
                    }

                    // Fix null pid for old export files
                    if (!isset($articleModel->pid) || '' == $articleModel->pid) {
                        $articleModel->pid = 0;
                    }

                    // Fix incorrect integer values for older export files
                    $this->correctData($this->articleFields, $articleModel);

                    $articleModel->save();
                    $this->replacementIds['articlePid'.$lastId] = $articleModel->id;
                    ++$articleCounter;
                }
            }
        }

        return $articleCounter;
    }

    /**
     * @throws \Exception
     */
    protected function importContent($filesModel): int
    {
        $contentCounter = 0;

        // Set contentFields
        foreach ($GLOBALS['TL_DCA']['tl_content']['fields'] as $key => $field) {
            if (is_array($field['sql']) && 'boolean' === $field['sql']['type']) {
                $this->contentFields[] = $key;
            }
        }

        foreach ($filesModel as $fileModel) {
            if ('file' === $fileModel->type) {
                if (str_starts_with($fileModel->name, 'content')) {
                    $modelArr = $this->getModelFileContent($fileModel->path);
                    $contentModel = new ContentModel();

                    foreach ($modelArr as $key => $value) {
                        // prevent setting id
                        if ('id' === $key) {
                            continue;
                        }

                        if ('pid' === $key) {
                            $contentModel->pid = $this->replacementIds['articlePid'.$value];
                            continue;
                        }

                        $contentModel->{$key} = $value;
                    }

                    // Fix incorrect integer values for older export files
                    $this->correctData($this->contentFields, $contentModel);

                    $contentModel->save();
                    ++$contentCounter;
                }
            }
        }

        return $contentCounter;
    }

    /**
     * @throws \Exception
     */
    protected function getModelFileContent(string $path): array
    {
        $file = new File($path);
        $content = unserialize($file->getContent());

        if (\is_array($content)) {
            return $content;
        }
    }

    protected function correctData($fields, $model): void {
        // Fix incorrect integer values for older export files
        foreach ($fields as $value) {
            if (!isset($model->{$value}) || '' == $model->{$value}) {
                $model->{$value} = 0;
            }
        }
    }
}
