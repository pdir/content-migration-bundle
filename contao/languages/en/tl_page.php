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

$GLOBALS['TL_LANG']['tl_page']['export'] = ['Export', 'Export data to a model file'];
$GLOBALS['TL_LANG']['tl_page']['import'] = ['Import', 'Import data from a model export'];

/*
 * Reference
 */
$GLOBALS['TL_LANG']['tl_page']['export_description'] = 'Here you can export whole pages, with subpages and their contents as a Contao Model to a file.';
$GLOBALS['TL_LANG']['tl_page']['export_exportName'] = ['Export name', 'Specify a name for your export, if nothing is specified, a random name will be generated for your export.'];
$GLOBALS['TL_LANG']['tl_page']['export_type'] = ['Export type', 'Choose what type of export you want to perform.'];
$GLOBALS['TL_LANG']['tl_page']['export_calendar'] = ['Export calendar', 'Do you want to export the calendar archives and events as well?'];
$GLOBALS['TL_LANG']['tl_page']['export_news'] = ['Export news', 'Should the news archives and news be exported as well?'];
$GLOBALS['TL_LANG']['tl_page']['export_inserttags'] = ['Convert InsertTags', 'InsertTags will be converted to HTML for export.'];
$GLOBALS['TL_LANG']['tl_page']['export_message']['complete'] = 'Successfully exported %d pages, %d articles and %d content items to %s.';
$GLOBALS['TL_LANG']['tl_page']['export_pageId'] = ['Page ID', 'You have selected a specific node in the page structure, this node will be used as the basis for the export.'];
$GLOBALS['TL_LANG']['tl_page']['export_userFolder'] = 'User folder:';

$GLOBALS['TL_LANG']['tl_page']['export_typeRef'] = [
    'full' => 'Full export (complete page structure and content)',
    'page' => 'Single page (with content)',
    'content' => 'Content of a page (only articles of a specific page) [coming soon]',
];

$GLOBALS['TL_LANG']['tl_page']['import_description'] = 'Here you can import whole pages with subpages and their content from a Contao model export into an existing page. New IDs are assigned during the import.';
$GLOBALS['TL_LANG']['tl_page']['import_type'] = ['Import type', 'Choose what kind of import you want to perform.'];
$GLOBALS['TL_LANG']['tl_page']['import_name'] = ['Name of the folder for import', 'Please select here the folder from which you want to import data'];
// $GLOBALS['TL_LANG']['tl_page']['import_considerFilters'] = ['Consider list filters', 'Export only the rows that were filtered in the list view.'];
// $GLOBALS['TL_LANG']['tl_page']['import_headerFields'] = ['Include cell header'];
// $GLOBALS['TL_LANG']['tl_page']['import_raw'] = ['Raw data only'];
$GLOBALS['TL_LANG']['tl_page']['import_message']['complete'] = 'It successfully imported %d pages, %d articles and %d content items, the new ID of the starting point is <strong>%d</strong>.';
$GLOBALS['TL_LANG']['tl_page']['import_userFolder'] = $GLOBALS['TL_LANG']['tl_page']['export_userFolder'];

$GLOBALS['TL_LANG']['tl_page']['import_typeRef'] = [
    'full' => 'Full import (Complete page structure and content)',
    // 'page' => 'Single page (with content)',
    // 'content' => 'Content of a page (only articles of a specific page)',
];
