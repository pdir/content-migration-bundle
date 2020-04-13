<?php

array_insert($GLOBALS['TL_DCA']['tl_page']['list']['global_operations'], 0, [
    'import' => [
        'label' => &$GLOBALS['TL_LANG']['tl_page']['import'],
        'href' => 'key=import',
        'icon' => 'bundles/pdircontentmigration/icons/002-import.svg',
        'class' => 'pdir_content_migration_import',
        'attributes' => 'onclick="Backend.getScrollOffset()"',
    ],
]);
