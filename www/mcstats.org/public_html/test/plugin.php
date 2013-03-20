<?php
define('ROOT', '../');
session_start();

require_once ROOT . '../private_html/config.php';
require_once ROOT . '../private_html/includes/database.php';
require_once ROOT . '../private_html/includes/func.php';

$pluginName = isset($_GET['plugin']) ? $_GET['plugin'] : null;

if ($pluginName == null) {
    exit ('0');
}

$plugin = loadPlugin($pluginName);
echo $plugin === null ? 0 : 1;