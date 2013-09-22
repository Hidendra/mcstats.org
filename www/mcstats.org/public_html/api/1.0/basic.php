<?php
define('ROOT', '../../');
header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');

require_once ROOT . '../private_html/config.php';
require_once ROOT . '../private_html/includes/database.php';
require_once ROOT . '../private_html/includes/func.php';

cacheCurrentPage();

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
$response['rank'] = $plugin->getRank();

// Server data
$response['servers'][24] = $plugin->getServerCount();

try {
    $globalGraph = $plugin->getOrCreateGraph('Global Statistics');

    $response['servers']['last'] = $plugin->getTimelineCustomLast($globalGraph->getColumnID('Servers'), $globalGraph);
    $response['players']['last'] = $plugin->getTimelineCustomLast($globalGraph->getColumnID('Players'), $globalGraph);
} catch (Exception $e) {
    //
}

$response['status'] = 'ok';
echo json_encode($response);