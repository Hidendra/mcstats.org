<?php

define('ROOT', '../private_html/');

require_once ROOT . 'config.php';
require_once ROOT . 'includes/database.php';
require_once ROOT . 'includes/func.php';

// Load all of the countries we can use
$countries = loadCountries();
$baseEpoch = normalizeTime();
$minimum = strtotime('-30 minutes', $baseEpoch);

function doGeneration($pluginId, $data) {
    global $countries, $baseEpoch;
    $plugin = loadPluginByID($pluginId);
    $java_name = $data['java_name'];
    $java_version = $data['java_version'];
    $sum = $data['Sum'];
    $count = $data['Count'];
    $avg = $data['Avg'];
    $max = $data['Max'];
    $min = $data['Min'];
    $variance = $data['Variance'];
    $stddev = $data['StdDev'];

    $fullName = $java_name . '~=~' . $java_version;

    if ($count == 0 || $sum == 0) {
        return;
    }

    if ($java_name == '' || $java_name == 'Unknown' || $java_version == '' || $java_version == 'Unknown') {
        return;
    }

    $graph = $plugin->getOrCreateGraph('Java Version', false, 1, GraphType::Donut, true, 9013, true);
    insertGraphData($graph, $pluginId, $fullName, $baseEpoch, $sum, $count, $avg, $max, $min, $variance, $stddev);
}

// Plugins
$statement = get_slave_db_handle()->prepare('
        SELECT
            Plugin,
            java_name,
            java_version,
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
        GROUP BY Plugin, java_name, java_version');
$statement->execute(array($minimum));

while ($row = $statement->fetch()) {
    doGeneration($row['Plugin'], $row);
}

// global plugin
$statement = get_slave_db_handle()->prepare('
                    SELECT
                        java_name,
                        java_version,
                        SUM(1) AS Sum,
                        COUNT(dev.Server) AS Count,
                        AVG(1) AS Avg,
                        MAX(1) AS Max,
                        MIN(1) AS Min,
                        VAR_SAMP(1) AS Variance,
                        STDDEV_SAMP(1) AS StdDev
                    FROM (
                      SELECT DISTINCT Server, Server.Players, Server.java_name AS java_name, Server.java_version as java_version
                      FROM ServerPlugin
                      LEFT OUTER JOIN Server ON Server.ID = ServerPlugin.Server
                      WHERE ServerPlugin.Updated >= ?
                    ) dev
                    GROUP BY java_name, java_version');
$statement->execute(array($minimum));

while ($row = $statement->fetch()) {
    doGeneration(GLOBAL_PLUGIN_ID, $row);
}