<?php
define('ROOT', '../../');
header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');

require_once ROOT . '../private_html/config.php';
require_once ROOT . '../private_html/includes/database.php';
require_once ROOT . '../private_html/includes/func.php';

cacheCurrentPage(array('api' => 1));

// Our json encoded response
$response = array();

if (!isset($_GET['page'])) {
    $page = 1;
} else {
    $page = intval($_GET['page']);
}

// get the total number of plugins
$totalPlugins = countPlugins(PLUGIN_ORDER_POPULARITY);
$response['maxPages'] = ceil($totalPlugins / PLUGIN_LIST_RESULTS_PER_PAGE);

// offset is how many plugins to start after
$offset = ($page - 1) * PLUGIN_LIST_RESULTS_PER_PAGE;
foreach (loadPlugins(PLUGIN_ORDER_POPULARITY, PLUGIN_LIST_RESULTS_PER_PAGE, $offset) as $plugin) {
    if ($plugin->isHidden()) {
        continue;
    }

    // count the number of servers in the last 24 hours
    $servers24 = $plugin->getServerCount();

    // add the plugin
    $response['plugins'][] = array(
        'rank' => $plugin->getRank(),
        'lastrank' => $plugin->getLastRank(),
        'name' => htmlentities($plugin->getName()),
        'authors' => htmlentities($plugin->getAuthors()),
        'servers24' => number_format($servers24)
    );
}

$response['status'] = 'ok';
echo json_encode($response);