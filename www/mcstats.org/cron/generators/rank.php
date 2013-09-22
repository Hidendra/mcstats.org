<?php

define('ROOT', '../private_html/');

require_once ROOT . 'config.php';
require_once ROOT . 'includes/database.php';
require_once ROOT . 'includes/func.php';

$baseEpoch = normalizeTime();

foreach (loadPlugins(PLUGIN_ORDER_ALPHABETICAL) as $plugin) {
    if ($plugin->getID() == GLOBAL_PLUGIN_ID) {
        continue;
    }

    $graph = $plugin->getOrCreateGraph('Rank', false, 1, GraphType::Area, true, 8601);
    insertGraphData($graph, $plugin->getID(), 'Rank', $baseEpoch, $plugin->getRank(), 1, 1, 1, 1, 0, 0);
}