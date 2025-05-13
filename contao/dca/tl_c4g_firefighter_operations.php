<?php

declare(strict_types=1);

use Contao\ArrayUtil;
use Contao\CoreBundle\DataContainer\PaletteManipulator;

if (!isset($GLOBALS['TL_DCA']['tl_c4g_firefighter_operations'])) {
    return;
}

// Add global operations
ArrayUtil::arrayInsert($GLOBALS['TL_DCA']['tl_c4g_firefighter_operations']['list']['global_operations'], 0, [
    'import' => [
        'label' => &$GLOBALS['TL_LANG']['tl_c4g_firefighter_operations']['import'],
        'href' => 'key=import',
        'icon' => 'bundles/pdircontentmigration/icons/002-import.svg',
        'class' => 'pdir_content_migration firefighter_import',
        'attributes' => 'onclick="Backend.getScrollOffset()"',
    ],
]);

// Add fields
if (!isset($GLOBALS['TL_DCA']['tl_c4g_firefighter_operations']['fields']['counter'])) {
    PaletteManipulator::create()
        ->addField('counter', 'publish_legend', PaletteManipulator::POSITION_APPEND)
        ->applyToPalette('default', 'tl_c4g_firefighter_operations')
    ;

    $GLOBALS['TL_DCA']['tl_c4g_firefighter_operations']['fields']['counter'] = [
        'inputType'=>'text',
        'eval'=>['tl_class'=>'w50'],
        'sql' => "int(11) NOT NULL default '0'"
    ];
}
