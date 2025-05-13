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

$GLOBALS['TL_LANG']['tl_news_archive']['import'] = ['Import', 'Import news'];

/*
 * Reference
 */
$GLOBALS['TL_LANG']['tl_news_archive']['import_description'] = 'Here you can import the news from a TYPO3 installation (extension tt_news). You need access to the database from the server of the Contao installation. Images must have already been copied to the files folder via FTP.';
$GLOBALS['TL_LANG']['tl_news_archive']['import_type'] = ['Import type'];
$GLOBALS['TL_LANG']['tl_news_archive']['typo3_tt_news_domain'] = ['Domain', 'Please enter the domain of the TYPO3 installation here.'];
$GLOBALS['TL_LANG']['tl_news_archive']['typo3_tt_news_host'] = ['Server', 'Please enter the TYPO3 database server you want to import data from'];
$GLOBALS['TL_LANG']['tl_news_archive']['typo3_tt_news_user'] = ['User', 'Please enter the TYPO3 database user here.'];
$GLOBALS['TL_LANG']['tl_news_archive']['typo3_tt_news_password'] = ['Password', 'Please enter the TYPO3 database user here.'];
$GLOBALS['TL_LANG']['tl_news_archive']['typo3_tt_news_database'] = ['database', 'Please enter the TYPO3 database here.'];
$GLOBALS['TL_LANG']['tl_news_archive']['typo3_tt_news_lng'] = ['UID of the language', 'Please enter here the TYPO3 UID of the desired language.'];
$GLOBALS['TL_LANG']['tl_news_archive']['typo3_tt_news_pid'] = ['PID of the news archive', 'Please enter here the TYPO3 PID of the desired news archive.'];
$GLOBALS['TL_LANG']['tl_news_archive']['typo3_tt_news_contao_news_archive'] = ['News archive', 'Select here the news archive you want to import into.'];
$GLOBALS['TL_LANG']['tl_news_archive']['typo3_tt_news_contao_news_author'] = ['Author', 'Select the author to import here.'];
$GLOBALS['TL_LANG']['tl_news_archive']['typo3_tt_news_image_folder'] = ['New image directory', 'Specify here the directory where you put the images. Ex. files/uploads/pics/'];
$GLOBALS['TL_LANG']['tl_news_archive']['import_message']['complete'] = '%d messages and %d pictures were imported successfully.';

$GLOBALS['TL_LANG']['tl_news_archive']['import_typeRef'] = [
    'typo3_tt_news' => 'Import news from a TYPO3 tt_news table',
];
