<?php

namespace Pdir\ContentMigrationBundle\Exporter;

use Contao\BackendUser;
use Contao\CoreBundle\Framework\ContaoFramework;

class ModelExporter
{
    /**
     * @var ContaoFramework
     */
    protected $framework;

    /**
     * BaseExporter constructor.
     *
     * @param ContaoFramework $framework
     */

    /**
     * storage path
     *
     * @param string $storagePath
     */
    static $storagePath = 'files/contentMigration';

    public function __construct(ContaoFramework $framework)
    {
        $this->framework = $framework;
    }

    /**
     * {@inheritdoc}
     */
    public function export($config = null)
    {
        self::getPageModels();
    }

    /**
     * Get the models.
     *
     * @param ExportConfig $config
     *
     * @return Collection|null
     */
    protected function getPageModels($config = null)
    {
        /** @var PageModel $pageModel */
        $pageModel = $this->framework->getAdapter(PageModel::class);

        // Return all pages if no config was provided (BC) or we should not consider listing filters
        if ($config === null) {
            return $pageModel->findAll();
        }

        $helper = new DataContainerHelper('tl_page');
        $helper->buildProceduresAndValues();

        // Return all pages if there are no filters set
        if (count($columns = $helper->getProcedure()) === 0) {
            return $pageModel->findAll();
        }

        return $pageModel->findBy($columns, $helper->getValues());
    }

    /**
     * Get current user folder.
     *
     * @return array
     *
     * @codeCoverageIgnore
     */
    public function getCurrentUserFolder()
    {
        $objUser = BackendUser::getInstance();
        return self::$storagePath . '/' . $objUser->username;
    }
}
