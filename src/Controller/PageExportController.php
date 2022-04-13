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

use Contao\ArticleModel;
use Contao\BackendTemplate;
use Contao\ContentModel;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\Environment;
use Contao\File;
use Contao\Message;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\System;
use Contao\TextField;
use Pdir\ContentMigrationBundle\Exporter\ModelExporter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class PageExportController
{
    private ContaoFramework $framework;

    private RequestStack $requestStack;

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
        $formId = 'tl_page_export';

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
     *
     * @throws \Exception
     */
    protected function processForm(Request $request): void
    {
        $userFolder = ModelExporter::getCurrentUserFolder();

        $folder = $request->get('exportName');
        $userFolder .= '/'.('' !== $folder ? $folder : uniqid());

        $pageCounter = 0;
        $articleCounter = 0;
        $elementCounter = 0;
        $pages = null;

        $exportType = $request->get('type');
        $pageId = $request->get('pageId') ?? null;

        switch ($exportType) {
            case 'full':
                try {
                    $pages = $this->getPages($pageId);
                } catch (ExportException $e) {
                    /** @var Message $message */
                    $message = $this->framework->getAdapter(Message::class);
                    $message->addError($e->getMessage());
                }
                break;

            case 'page':
                $pages = PageModel::findById($pageId);
                break;

            case 'content':
                /** @var Message $message */
                $message = $this->framework->getAdapter(Message::class);
                $message->addError('Not available yet.');
                break;
        }

        if (null !== $pages) {
            foreach ($pages as $page) {
                $ids = [];
                $ids[] = 'p'.$this->withLeadingZeroes($page->pid);
                $ids[] = 'id'.$this->withLeadingZeroes($page->id);

                // write page model
                $this->saveSerializeFile(
                    $userFolder,
                    $ids,
                    $page->row(),
                    'page'
                );

                // write article model
                /** @var ArticleModel $articleModel */
                $articleModel = ArticleModel::findByPid([$page->id]);

                if (null !== $articleModel) {
                    foreach ($articleModel as $article) {
                        $this->saveSerializeFile(
                            $userFolder,
                            ['id'.$this->withLeadingZeroes($article->id), 'pid'.$this->withLeadingZeroes($article->pid)],
                            $article->row(),
                            'article'
                        );

                        /** @var ContentModel $contentModel */
                        $contentModel = ContentModel::findByPid([$article->id]);

                        if (null !== $contentModel) {
                            foreach ($contentModel as $content) {
                                $this->saveSerializeFile(
                                    $userFolder,
                                    ['pid'.$this->withLeadingZeroes($article->pid), 'id'.$this->withLeadingZeroes($content->id), ($content->ptable ?: 'null')],
                                    $content->row(),
                                    'content'
                                );

                                ++$elementCounter;
                            }
                        }

                        /** @var ModuleModel $moduleModel */
                        //$moduleModel = ModuleModel::findByPid([$article->id]);

                        /** @var NewsArchiveModel $newsArchiveModel */
                        //$newsArchiveModel = NewsArchiveModel::findByPid([$article->id]);

                        /** @var NewsModel $newsModel */
                        //$newsModel = NewsModel::findByPid([$article->id]);

                        /** @var CalendarModel $calendarModel */
                        //$calendarModel = CalendarModel::findByPid([$article->id]);

                        /** @var CalendarEventsModel $calendarEventsModel */
                        //$calendarEventsModel = CalendarEventsModel::findByPid([$article->id]);

                        ++$articleCounter;
                    }
                }
                // write content model

                ++$pageCounter;
            }

            $message = $this->framework->getAdapter(Message::class);
            $message->addConfirmation(sprintf($GLOBALS['TL_LANG']['tl_page']['export_message']['complete'], $pageCounter, $articleCounter, $elementCounter, $userFolder));
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

        /** @var AttributeBagInterface $objSession */
        $objSession = System::getContainer()->get('session')->getBag('contao_backend');
        $intNode = $objSession->get('tl_page_node');

        $template = new BackendTemplate('be_page_export');
        $template->backUrl = $system->getReferer();
        $template->action = $environment->get('request');
        $template->formId = $formId;
        $template->typeOptions = $this->generateTypeOptions();
        $template->message = $message->generate();
        $template->currentUserFolder = ModelExporter::getCurrentUserFolder();

        //// widgets
        // page id widget
        if (0 !== $intNode) {
            $widgetPageId = new TextField();

            $widgetPageId->label = $GLOBALS['TL_LANG']['tl_page']['export_pageId'][0];
            $widgetPageId->name = 'pageId';
            $widgetPageId->value = $intNode ?? '';

            $template->widgetPageId = $widgetPageId->parse();
        }

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

        foreach ($GLOBALS['TL_LANG']['tl_page']['export_typeRef'] as $alias => &$label) {
            $options[$alias] = $GLOBALS['TL_LANG']['tl_page']['export_typeRef'][$alias];
        }
        unset($label);

        return $options;
    }

    /**
     * Generate the options.
     *
     * @codeCoverageIgnore
     */
    protected function generateOptions(): array
    {
        return [];
    }

    protected function getPages($page = null)
    {
        if (null === $page) {
            /** @var PageModel $pageModel */
            $pageModel = $this->framework->getAdapter(PageModel::class);

            return $pageModel->findAll();
        }

        return $this->getChildPages($page);
    }

    protected function getChildPages($id)
    {
        $objDatabase = Database::getInstance();
        $arrPages = $objDatabase->getChildRecords($id, 'tl_page');

        // add selected page
        array_unshift($arrPages, $id);

        if (\is_array($arrPages) && 0 < \count($arrPages)) {
            $pageModel = $this->framework->getAdapter(PageModel::class);
            $pages = $pageModel->findMultipleByIds($arrPages);

            if (null !== $pages) {
                return $pages;
            }
        }
    }

    /**
     * Generate number with leading zeroes.
     */
    protected function withLeadingZeroes(string $number): string
    {
        return sprintf('%08d', $number);
    }

    /**
     * @throws \Exception
     */
    protected function saveSerializeFile($folder, $parts, $data, $type): void
    {
        $filename = implode('-', $parts);
        $file = new File($folder.\DIRECTORY_SEPARATOR.$type.'.'.$filename.ModelExporter::$fileExt);
        $file->write(serialize($data));
        $file->close();
    }
}
