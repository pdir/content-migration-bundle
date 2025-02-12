<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;

// Add fields
if (!isset($GLOBALS['TL_DCA']['tl_c4g_firefighter_units']['fields']['counter'])) {
    PaletteManipulator::create()
        ->addField('link', 'custom_legend', PaletteManipulator::POSITION_APPEND)
        ->addField('geox', 'custom_legend', PaletteManipulator::POSITION_APPEND)
        ->addField('geoy', 'custom_legend', PaletteManipulator::POSITION_APPEND)
        ->addField('detail', 'custom_legend', PaletteManipulator::POSITION_APPEND)
        ->applyToPalette('default', 'tl_c4g_firefighter_units')
    ;

    $GLOBALS['TL_DCA']['tl_c4g_firefighter_units']['fields']['link'] = [
        'inputType'=>'text',
        'eval'=>['tl_class'=>'w50'],
        'sql' => "varchar(255) NOT NULL default ''"
    ];

    $GLOBALS['TL_DCA']['tl_c4g_firefighter_units']['fields']['geox'] = [
        'inputType'=>'text',
        'eval'=>['tl_class'=>'w50'],
        'sql' => "varchar(255) NOT NULL default ''"
    ];

    $GLOBALS['TL_DCA']['tl_c4g_firefighter_units']['fields']['geoy'] = [
        'inputType'=>'text',
        'eval'=>['tl_class'=>'w50'],
        'sql' => "varchar(255) NOT NULL default ''"
    ];

    $GLOBALS['TL_DCA']['tl_c4g_firefighter_units']['fields']['detail'] = [
        'inputType'=>'text',
        'eval'=>['tl_class'=>'w50'],
        'sql' => "varchar(255) NOT NULL default ''"
    ];
}
