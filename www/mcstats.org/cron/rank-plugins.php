<?php

define('ROOT', '../private_html/');

require_once ROOT . 'config.php';
require_once ROOT . 'includes/database.php';
require_once ROOT . 'includes/func.php';

// array of plugin objects
$plugins = array();

// array of plugin server counts (1d)
$counts = array();

// count servers
foreach (loadPlugins(PLUGIN_ORDER_SERVERCOUNT30) as $plugin) {
    if ($plugin->isHidden()) {
        continue;
    }

    $plugins[$plugin->getID()] = $plugin;
    $counts[$plugin->getID()] = $plugin->getServerCount();
}

// sort the plugins
arsort($counts);

$rank = 0;
$lastChange = normalizeTime();
foreach ($counts as $pluginId => $count) {
    $plugin = $plugins[$pluginId];

    $newRank = ++$rank;

    // did their rank change ?
    if ($newRank != $plugin->getRank()) {
        $plugin->setLastRankChange($lastChange);
    }

    $plugin->setLastRank($plugin->getRank());
    $plugin->setRank($newRank);
    $plugin->save();

    echo sprintf('%d: %s%s', $rank, $plugin->getName(), PHP_EOL);
}