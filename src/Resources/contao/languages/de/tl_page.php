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

$GLOBALS['TL_LANG']['tl_page']['export'] = ['Export', 'Daten in eine Model Datei exportieren'];
$GLOBALS['TL_LANG']['tl_page']['import'] = ['Import', 'Daten aus einem Model Export importieren'];

/*
 * Reference
 */
$GLOBALS['TL_LANG']['tl_page']['export_description'] = 'Hier kannst du ganze Seiten, mit Unterseiten und deren Inhalte als Contao Model in eine Datei exportieren.';
$GLOBALS['TL_LANG']['tl_page']['export_exportName'] = ['Export-Name', 'Gib einen Namen für deinen Export an, wird nichts angegeben, wird ein zufälliger Name für deinen Export generiert.'];
$GLOBALS['TL_LANG']['tl_page']['export_type'] = ['Export Typ', 'Wähle welche Art von Export du durchführen möchtest.'];
$GLOBALS['TL_LANG']['tl_page']['export_calendar'] = ['Kalender exportieren', 'Sollen die Kalenderarchive und Veranstaltungen mit exportiert werden?'];
$GLOBALS['TL_LANG']['tl_page']['export_news'] = ['News exportieren', 'Sollen die Newsarchive und Nachrichten mit exportiert werden?'];
$GLOBALS['TL_LANG']['tl_page']['export_inserttags'] = ['InsertTags umwandeln', 'InsertTags werden für den Export in HTML umgewandelt.'];
$GLOBALS['TL_LANG']['tl_page']['export_message']['complete'] = 'Es wurden erfolgreich %d Seiten, %d Artikel und %d Inhaltselemente nach %s exportiert.';
$GLOBALS['TL_LANG']['tl_page']['export_pageId'] = ['Page ID', 'Du hast in der Seitenstruktur einen bestimmten Knoten ausgewählt, dieser dient als Basis für den Export.'];
$GLOBALS['TL_LANG']['tl_page']['export_userFolder'] = 'Benutzerordner:';

$GLOBALS['TL_LANG']['tl_page']['export_typeRef'] = [
    'full' => 'Vollexport (Komplette Seitenstruktur und Inhalte)',
    'page' => 'Einzelne Seite (mit Inhalt)',
    'content' => 'Inhalt einer Seite (nur Artikel einer bestimmten Seite) [demnächst verfügbar]',
];

$GLOBALS['TL_LANG']['tl_page']['import_description'] = 'Hier kannst du ganze Seiten, mit Unterseiten und deren Inhalte aus einem Contao Model Export in eine bestehende Seite importieren. Es werden neue IDs beim import vergeben.';
$GLOBALS['TL_LANG']['tl_page']['import_type'] = ['Import Typ', 'Wähle welche Art von Import du durchführen möchtest.'];
$GLOBALS['TL_LANG']['tl_page']['import_name'] = ['Name des Ordners für den Import', 'Wähle hier bitte den Ordner aus dem du Daten importieren möchtest.'];
// $GLOBALS['TL_LANG']['tl_page']['import_considerFilters'] = ['Listenfilter berücksichtigen', 'Nur die Zeilen exportieren, die in der Listenansicht gefiltert wurden.'];
// $GLOBALS['TL_LANG']['tl_page']['import_headerFields'] = ['Inklusive Zellenüberschrift'];
// $GLOBALS['TL_LANG']['tl_page']['import_raw'] = ['Nur Rohdaten'];
$GLOBALS['TL_LANG']['tl_page']['import_message']['complete'] = 'Es wurden %d Seiten, %d Artikel und %d Inhaltselemente erfolgreich importiert, die neue ID des Startpunktes lautet <strong>%d</strong>.';
$GLOBALS['TL_LANG']['tl_page']['import_userFolder'] = $GLOBALS['TL_LANG']['tl_page']['export_userFolder'];

$GLOBALS['TL_LANG']['tl_page']['import_typeRef'] = [
    'full' => 'Vollimport (Komplette Seitenstruktur und Inhalte)',
    // 'page' => 'Einzelne Seite (mit Inhalt)',
    // 'content' => 'Inhalt einer Seite (nur Artikel einer bestimmten Seite)',
];
