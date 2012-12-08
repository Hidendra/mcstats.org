<?php
define('ROOT', '../public_html/');

require_once ROOT . 'config.php';
require_once ROOT . 'includes/database.php';
require_once ROOT . 'includes/func.php';
require_once ROOT . 'pChart/pCache.class.php';

// signature / plugin preview images
$pCache = new pCache('../cache/');
$pCache->ClearCache();

// memcached
$cache->handle()->flush();

// mysql
$master_db_handle->exec('TRUNCATE CustomData');