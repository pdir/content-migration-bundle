<?php

/*
 * Backend modules
 */
$GLOBALS['BE_MOD']['design']['page']['import'] = ['pdir_content_migration_import.controller', 'run'];

/*
 * Css
 */
if (TL_MODE == 'BE')
{
    $GLOBALS['TL_CSS'][] = 'bundles/pdircontentmigration/css/pdir_cm_backend.css|screen';
}
