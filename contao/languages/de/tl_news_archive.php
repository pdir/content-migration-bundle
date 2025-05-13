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

$GLOBALS['TL_LANG']['tl_news_archive']['import'] = ['Import', 'News importieren'];

/*
 * Reference
 */
$GLOBALS['TL_LANG']['tl_news_archive']['import_description'] = 'Hier kannst du die News aus einer TYPO3 Installation (Extension tt_news) importieren. Du benötigst Zugriff auf die Datenbank vom Server der Contao Installation aus. Bilder müssen bereits per FTP in den files Ordner kopiert wurden sein.';
$GLOBALS['TL_LANG']['tl_news_archive']['import_type'] = ['Import Typ'];
$GLOBALS['TL_LANG']['tl_news_archive']['typo3_tt_news_domain'] = ['Domain', 'Trage hier bitte die Domain der TYPO3 Installation ein.'];
$GLOBALS['TL_LANG']['tl_news_archive']['typo3_tt_news_host'] = ['Server', 'Trage hier bitte den TYPO3 Datenbank Server ein, von dem du Daten importieren möchtest.'];
$GLOBALS['TL_LANG']['tl_news_archive']['typo3_tt_news_user'] = ['Benutzer', 'Trage hier bitte den TYPO3 Datenbank Benutzer ein.'];
$GLOBALS['TL_LANG']['tl_news_archive']['typo3_tt_news_password'] = ['Passwort', 'Trage hier bitte den TYPO3 Datenbank Benutzer ein.'];
$GLOBALS['TL_LANG']['tl_news_archive']['typo3_tt_news_database'] = ['Datenbank', 'Trage hier bitte die TYPO3 Datenbank ein.'];
$GLOBALS['TL_LANG']['tl_news_archive']['typo3_tt_news_lng'] = ['UID der Sprache', 'Trage hier bitte die TYPO3 UID der gewünschten Sprache ein.'];
$GLOBALS['TL_LANG']['tl_news_archive']['typo3_tt_news_pid'] = ['PID des Nachrichtenarchiv', 'Trage hier bitte die TYPO3 PID des gewünschten Nachrichtenarchiv ein.'];
$GLOBALS['TL_LANG']['tl_news_archive']['typo3_tt_news_contao_news_archive'] = ['Nachrichtenarchiv', 'Wähle hier das Nachrichtenarchiv in das du importieren möchtest.'];
$GLOBALS['TL_LANG']['tl_news_archive']['typo3_tt_news_contao_news_author'] = ['Autor', 'Wähle hier den Autor für den Import.'];
$GLOBALS['TL_LANG']['tl_news_archive']['typo3_tt_news_image_folder'] = ['Neues Bildverzeichnis', 'Gib hier das Verzeichnis an, in dem du die Bilder ablegt hast. Bsp. files/uploads/pics/'];
$GLOBALS['TL_LANG']['tl_news_archive']['import_message']['complete'] = 'Es wurden %d Nachrichten und %d Bilder erfolgreich importiert.';

$GLOBALS['TL_LANG']['tl_news_archive']['import_typeRef'] = [
    'typo3_tt_news' => 'News aus einer TYPO3 tt_news Tabelle importieren',
];
