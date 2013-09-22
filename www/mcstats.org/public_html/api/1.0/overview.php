<?php
define('ROOT', '../../');
header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');

require_once ROOT . '../private_html/config.php';
require_once ROOT . '../private_html/includes/database.php';
require_once ROOT . '../private_html/includes/func.php';

cacheCurrentPage();

$response['plugins'] = array(
    'total' => intval(countPlugins(PLUGIN_ORDER_ALPHABETICAL)),
    'active' => intval(countPlugins(PLUGIN_ORDER_POPULARITY))
);

$response['status'] = 'ok';
echo json_encode($response);