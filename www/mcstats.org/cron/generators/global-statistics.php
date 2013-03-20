<?php

define('ROOT', '../private_html/');

require_once ROOT . 'config.php';
require_once ROOT . 'includes/database.php';
require_once ROOT . 'includes/func.php';

// the current number of running forks
$running_processes = 0;

$baseEpoch = normalizeTime();
$minimum = strtotime('-30 minutes', $baseEpoch);

function doGeneration($pluginId, $data) {
    global $baseEpoch;
    $plugin = loadPluginByID($pluginId);
    $sum = $data['Sum'];
    $count = $data['Count'];
    $avg = $data['Avg'];
    $max = $data['Max'];
    $min = $data['Min'];
    $variance = $data['Variance'];
    $stddev = $data['StdDev'];

    if ($count == 0 || $sum == 0) {
        return;
    }

    $graph = $plugin->getOrCreateGraph('Global Statistics', false, 1, GraphType::Area, true, 1);

    // players
    insertGraphDataScratch($graph, $pluginId, 'Players', $baseEpoch, $sum, $count, $avg, $max, $min, $variance, $stddev);

    // servers
    insertGraphDataScratch($graph, $pluginId, 'Servers', $baseEpoch, $count, $count, 1, 1, 1, 1, 1);
}

// Plugins
$statement = get_slave_db_handle()->prepare('
        SELECT
            Plugin,
            SUM(Players) AS Sum,
            COUNT(*) AS Count,
            AVG(Players) AS Avg,
            MAX(Players) AS Max,
            MIN(Players) AS Min,
            VAR_SAMP(Players) AS Variance,
            STDDEV_SAMP(Players) AS StdDev
        FROM ServerPlugin
        LEFT OUTER JOIN Server ON Server.ID = ServerPlugin.Server
        WHERE Updated >= ?
        GROUP BY Plugin');
$statement->execute(array($minimum));

while ($row = $statement->fetch()) {
    doGeneration($row['Plugin'], $row);
}

// global plugin
$statement = get_slave_db_handle()->prepare('
        SELECT
            SUM(Players) AS Sum,
            COUNT(*) AS Count,
            AVG(Players) AS Avg,
            MAX(Players) AS Max,
            MIN(Players) AS Min,
            VAR_SAMP(Players) AS Variance,
            STDDEV_SAMP(Players) AS StdDev
        FROM (
          SELECT DISTINCT Server, Server.Players
          FROM ServerPlugin
          LEFT OUTER JOIN Server ON Server.ID = ServerPlugin.Server
          WHERE ServerPlugin.Updated >= ?
        ) dev');
$statement->execute(array($minimum));

while ($row = $statement->fetch()) {
    doGeneration(GLOBAL_PLUGIN_ID, $row);
}