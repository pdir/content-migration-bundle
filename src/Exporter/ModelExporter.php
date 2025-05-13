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

namespace Pdir\ContentMigrationBundle\Exporter;

use Contao\BackendUser;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Folder;

class ModelExporter
{
    /**
     * BaseExporter constructor.
     *
     * @param ContaoFramework $framework
     */

    /**
     * storage path.
     *
     * @param string $framework
     */
    public static string $storagePath = 'files/contentMigration';

    public static string $fileExt = '.model';

    protected ContaoFramework $framework;

    public function __construct(ContaoFramework $framework)
    {
        $this->framework = $framework;
    }

    public function export($config = null): void
    {
        self::getPageModels();
    }

    /**
     * Get current user folder.
     *
     * @throws \Exception
     *
     * @codeCoverageIgnore
     */
    public static function getCurrentUserFolder(): string
    {
        $objUser = BackendUser::getInstance();

        $strFolder = self::$storagePath.'/'.$objUser->username;

        // create user folder if not exists
        if (!is_dir($strFolder)) {
            new Folder($strFolder);
        }

        return $strFolder;
    }

    /**
     * Get the models.
     */
    protected function getPageModels(ExportConfig $config = null): ?Collection
    {
        /** @var PageModel $pageModel */
        $pageModel = $this->framework->getAdapter(PageModel::class);

        // Return all pages if no config was provided (BC) or we should not consider listing filters
        if (null === $config) {
            return $pageModel->findAll();
        }

        $helper = new DataContainerHelper('tl_page');
        $helper->buildProceduresAndValues();

        // Return all pages if there are no filters set
        if (0 === \count($columns = $helper->getProcedure())) {
            return $pageModel->findAll();
        }

        return $pageModel->findBy($columns, $helper->getValues());
    }
}
