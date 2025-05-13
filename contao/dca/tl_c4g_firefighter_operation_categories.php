<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;

if (!isset($GLOBALS['TL_DCA']['tl_c4g_firefighter_operation_categories'])) {
    return;
}

// Add fields
if (!isset($GLOBALS['TL_DCA']['tl_c4g_firefighter_operation_categories']['fields']['counter'])) {
    PaletteManipulator::create()
        ->addField('singleSRC', 'data_legend', PaletteManipulator::POSITION_APPEND)
        ->applyToPalette('default', 'tl_c4g_firefighter_operation_categories')
    ;

    $GLOBALS['TL_DCA']['tl_c4g_firefighter_operation_categories']['fields']['singleSRC'] = [
        'inputType'=>'text',
        'eval'=>['tl_class'=>'w50'],
        'sql' => "blob NOT NULL default ''"
    ];
}
