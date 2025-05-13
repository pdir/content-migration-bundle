<?php

declare(strict_types=1);

use Contao\ArrayUtil;

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

ArrayUtil::arrayInsert($GLOBALS['TL_DCA']['tl_news_archive']['list']['global_operations'], 0, [
    'import' => [
        'label' => &$GLOBALS['TL_LANG']['tl_news_archive']['import'],
        'href' => 'key=import',
        'icon' => 'bundles/pdircontentmigration/icons/002-import.svg',
        'class' => 'pdir_content_migration news_import',
        'attributes' => 'onclick="Backend.getScrollOffset()"',
    ],
]);
