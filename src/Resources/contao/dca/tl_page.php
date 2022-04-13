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

array_insert($GLOBALS['TL_DCA']['tl_page']['list']['global_operations'], 0, [
    'export' => [
        'label' => &$GLOBALS['TL_LANG']['tl_page']['export'],
        'href' => 'key=export',
        'icon' => 'bundles/pdircontentmigration/icons/001-export.svg',
        'class' => 'pdir_content_migration page_export',
        'attributes' => 'onclick="Backend.getScrollOffset()"',
    ],
    'import' => [
        'label' => &$GLOBALS['TL_LANG']['tl_page']['import'],
        'href' => 'key=import',
        'icon' => 'bundles/pdircontentmigration/icons/002-import.svg',
        'class' => 'pdir_content_migration page_import',
        'attributes' => 'onclick="Backend.getScrollOffset()"',
    ],
]);
