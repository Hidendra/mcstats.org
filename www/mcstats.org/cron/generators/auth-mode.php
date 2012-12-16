<?php

define('ROOT', '../public_html/');

require_once ROOT . 'config.php';
require_once ROOT . 'includes/database.php';
require_once ROOT . 'includes/func.php';

// Load all of the countries we can use
$countries = loadCountries();
$baseEpoch = normalizeTime();
$minimum = strtotime('-30 minutes', $baseEpoch);

function doGeneration($pluginId, $data)
{
    global $countries, $baseEpoch;
    $plugin = loadPluginByID($pluginId);
    $auth_mode = $data['online_mode'];
    $sum = $data['Sum'];
    $count = $data['Count'];
    $avg = $data['Avg'];
    $max = $data['Max'];
    $min = $data['Min'];
    $variance = $data['Variance'];
    $stddev = $data['StdDev'];

    if ($auth_mode == -1) {
        return;
    }

    $auth_mode = $auth_mode == 1 ? 'Online' : 'Offline';
    $fullName = $auth_mode;

    if ($count == 0 || $sum == 0) {
        return;
    }

    $graph = $plugin->getOrCreateGraph('Auth Mode', false, 0, GraphType::Pie, TRUE, 9011);
    insertGraphDataScratch($graph, $pluginId, $fullName, $baseEpoch, $sum, $count, $avg, $max, $min, $variance, $stddev);
}

// Plugins
$statement = get_slave_db_handle()->prepare('
        SELECT
            Plugin,
            online_mode,
            SUM(1) AS Sum,
            COUNT(*) AS Count,
            AVG(1) AS Avg,
            MAX(1) AS Max,
            MIN(1) AS Min,
            VAR_SAMP(1) AS Variance,
            STDDEV_SAMP(1) AS StdDev
        FROM ServerPlugin
        LEFT OUTER JOIN Server ON Server.ID = ServerPlugin.Server
        WHERE Updated >= ?
        GROUP BY Plugin, online_mode');
$statement->execute(array($minimum));

while ($row = $statement->fetch()) {
    doGeneration($row['Plugin'], $row);
}

// global plugin
$statement = get_slave_db_handle()->prepare('
                    SELECT
                        online_mode,
                        SUM(1) AS Sum,
                        COUNT(dev.Server) AS Count,
                        AVG(1) AS Avg,
                        MAX(1) AS Max,
                        MIN(1) AS Min,
                        VAR_SAMP(1) AS Variance,
                        STDDEV_SAMP(1) AS StdDev
                    FROM (
                      SELECT DISTINCT Server, Server.Players, Server.online_mode
                      FROM ServerPlugin
                      LEFT OUTER JOIN Server ON Server.ID = ServerPlugin.Server
                      WHERE ServerPlugin.Updated >= ?
                    ) dev
                    GROUP BY online_mode');
$statement->execute(array($minimum));

while ($row = $statement->fetch()) {
    doGeneration(GLOBAL_PLUGIN_ID, $row);
}