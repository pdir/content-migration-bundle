<?php

$GLOBALS['TL_LANG']['tl_page']['import'] = ['Import', 'Daten aus einem Model Export importieren'];

/*
 * Reference
 */
$GLOBALS['TL_LANG']['tl_page']['import_description'] = 'Hier kannst du ganze Seiten, mit Unterseiten und deren Inhalte aus einem Contao Model Export in eine bestehende Seite importieren. Es werden neue IDs beim import vergeben.';
$GLOBALS['TL_LANG']['tl_page']['import_type'] = ['Import Typ'];
$GLOBALS['TL_LANG']['tl_page']['import_name'] = ['Name des Ordners für den Import', 'Wähle hier bitte den Ordner aus dem du Daten importieren möchtest.'];
$GLOBALS['TL_LANG']['tl_page']['import_considerFilters'] = ['Listenfilter berücksichtigen', 'Nur die Zeilen exportieren, die in der Listenansicht gefiltert wurden.'];
$GLOBALS['TL_LANG']['tl_page']['import_headerFields'] = ['Inklusive Zellenüberschrift'];
$GLOBALS['TL_LANG']['tl_page']['import_raw'] = ['Nur Rohdaten'];
$GLOBALS['TL_LANG']['tl_page']['import_message']['complete'] = 'Es wurden %d Seiten, %d Artikel und %d Inhaltselemente erfolgreich importiert, die neue ID des neuen Startpunkt lautet <strong>%d<strong>.';

$GLOBALS['TL_LANG']['tl_page']['import_typeRef'] = [
    'full' => 'Vollimport (Komplette Seitenstruktur und Inhalte)',
    // 'page' => 'Einzelne Seite (mit Inhalt)',
    // 'content' => 'Inhalt einer Seite (nur Artikel einer bestimmten Seite)',
];
