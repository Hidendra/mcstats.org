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
    $cores = $data['cores'];
    $sum = $data['Sum'];
    $count = $data['Count'];
    $avg = $data['Avg'];
    $max = $data['Max'];
    $min = $data['Min'];
    $variance = $data['Variance'];
    $stddev = $data['StdDev'];

    $fullName = $cores;

    if ($cores == 0 || $count == 0 || $sum == 0) {
        return;
    }

    $graph = $plugin->getOrCreateGraph('System Cores', false, 0, GraphType::Pie, TRUE, 9012);
    insertGraphDataScratch($graph, $pluginId, $fullName, $baseEpoch, $sum, $count, $avg, $max, $min, $variance, $stddev);
}

// Plugins
$statement = get_slave_db_handle()->prepare('
        SELECT
            Plugin,
            cores,
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
        GROUP BY Plugin, cores');
$statement->execute(array($minimum));

while ($row = $statement->fetch()) {
    doGeneration($row['Plugin'], $row);
}

// global plugin
$statement = get_slave_db_handle()->prepare('
                    SELECT
                        cores,
                        SUM(1) AS Sum,
                        COUNT(dev.Server) AS Count,
                        AVG(1) AS Avg,
                        MAX(1) AS Max,
                        MIN(1) AS Min,
                        VAR_SAMP(1) AS Variance,
                        STDDEV_SAMP(1) AS StdDev
                    FROM (
                      SELECT DISTINCT Server, Server.Players, Server.osarch
                      FROM ServerPlugin
                      LEFT OUTER JOIN Server ON Server.ID = ServerPlugin.Server
                      WHERE ServerPlugin.Updated >= ?
                    ) dev
                    GROUP BY cores');
$statement->execute(array($minimum));

while ($row = $statement->fetch()) {
    doGeneration(GLOBAL_PLUGIN_ID, $row);
}