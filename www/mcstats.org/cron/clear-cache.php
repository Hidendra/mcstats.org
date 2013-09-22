<?php
define('ROOT', '../private_html/');

require_once ROOT . 'config.php';
require_once ROOT . 'includes/database.php';
require_once ROOT . 'includes/func.php';
require_once ROOT . 'pChart/pCache.class.php';

// signature / plugin preview images
$pCache = new pCache('../cache/');
$pCache->ClearCache();

// mysql
$master_db_handle->exec('TRUNCATE CustomData');
$master_db_handle->exec('TRUNCATE VersionHistory');