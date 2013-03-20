<?php
define('ROOT', '../../');
header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');

require_once ROOT . '../private_html/config.php';
require_once ROOT . '../private_html/includes/database.php';
require_once ROOT . '../private_html/includes/func.php';

insert_cache_headers();

// Our json encoded response
$response = array();

if (!isset($_GET['plugin'])) {
    $response['msg'] = 'No plugin provided';
    $response['status'] = 'err';
    exit(json_encode($response));
}

$plugin = loadPlugin($_GET['plugin']);

if ($plugin === null) {
    $response['msg'] = 'Invalid plugin';
    $response['status'] = 'err';
    exit(json_encode($response));
}

// Add some basic data
$response['name'] = $plugin->getName(); // resend them the name so it is case-correct
$response['author'] = $plugin->getAuthors();
$response['starts'] = $plugin->getGlobalHits();

// Server data
$response['servers'][24] = $plugin->getServerCount();


$response['status'] = 'ok';
echo json_encode($response);