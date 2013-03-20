<?php

define('ROOT', '../private_html/');

require_once ROOT . 'config.php';
require_once ROOT . 'includes/database.php';
require_once ROOT . 'includes/func.php';

// number of plugins converted
$converted = 0;

$plugins = loadPlugins(PLUGIN_ORDER_ALPHABETICAL);
$total = count($plugins);

$statement = $master_db_handle->prepare('INSERT INTO GraphData (Plugin, ColumnID, Sum, Count, Avg, Max, Min, Variance, StdDev, Epoch)
                                            SELECT Plugin, :ColumnID, Servers, 0, 0, 0, 0, 0, 0, Epoch FROM ServerTimeline where Plugin = :Plugin');

// iterate through all of the plugins
foreach ($plugins as $plugin) {
    echo sprintf('[%d%%] Converting %s from ServerTimeline to the unified graphing format ..%s', floor(($converted / $total) * 100), $plugin->getName(), PHP_EOL);

    // get or create the graph
    $globalstats = $plugin->getOrCreateGraph('Global Statistics', false, 1, GraphType::Area, true);
    // get the column id
    $columnID = $globalstats->getColumnID('Servers');

    // convert all of it
    $statement->execute(array(':Plugin' => $plugin->getID(), ':ColumnID' => $columnID));
    $converted++;
}

echo sprintf('Converted %d plugins%s', $converted, PHP_EOL);