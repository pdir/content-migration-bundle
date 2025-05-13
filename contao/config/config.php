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

use Contao\System;
use Pdir\ContentMigrationBundle\Controller\FirefighterImportController;
use Pdir\ContentMigrationBundle\Controller\NewsImportController;
use Pdir\ContentMigrationBundle\Controller\PageExportController;
use Pdir\ContentMigrationBundle\Controller\PageImportController;
use Symfony\Component\HttpFoundation\Request;

$GLOBALS['BE_MOD']['design']['page']['export'] = [PageExportController::class, 'run'];
$GLOBALS['BE_MOD']['design']['page']['import'] = [PageImportController::class, 'run'];
$GLOBALS['BE_MOD']['content']['news']['import'] = [NewsImportController::class, 'run'];

if (isset($GLOBALS['BE_MOD']['con4gis']['c4g_firefighter_operations'])) {
    $GLOBALS['BE_MOD']['con4gis']['c4g_firefighter_operations']['import'] = [FirefighterImportController::class, 'run'];
}

/*
 * Css
 */
if (System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''))) {
    $GLOBALS['TL_CSS'][] = 'bundles/pdircontentmigration/css/pdir_cm_backend.css|screen';
}
