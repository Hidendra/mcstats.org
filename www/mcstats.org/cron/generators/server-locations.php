<?php

define('ROOT', '../private_html/');

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
    $sum = $data['Sum'];
    $countryShortCode = $data['Country'];
    $count = $data['Count'];
    $avg = $data['Avg'];
    $max = $data['Max'];
    $min = $data['Min'];
    $variance = $data['Variance'];
    $stddev = $data['StdDev'];

    if (!isset($countries[$countryShortCode])) {
        return;
    }

    // get the full country name
    $fullName = $countries[$countryShortCode];

    if ($count == 0 || $sum == 0) {
        return;
    }

    // Create the generic Map graph that shows a nice map ! :-)
    $plugin->getOrCreateGraph('Map', false, 1, GraphType::Map, TRUE, 9600);

    $graph = $plugin->getOrCreateGraph('Server Locations', false, 1, GraphType::Pie, TRUE, 9000);
    insertGraphDataScratch($graph, $pluginId, $fullName, $baseEpoch, $sum, $count, $avg, $max, $min, $variance, $stddev);
}

// Plugins
$statement = get_slave_db_handle()->prepare('
        SELECT
            Plugin,
            Country,
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
        GROUP BY Plugin, Country');
$statement->execute(array($minimum));

while ($row = $statement->fetch()) {
    doGeneration($row['Plugin'], $row);
}

// global plugin
$statement = get_slave_db_handle()->prepare('
                    SELECT
                        Country,
                        SUM(1) AS Sum,
                        COUNT(dev.Server) AS Count,
                        AVG(1) AS Avg,
                        MAX(1) AS Max,
                        MIN(1) AS Min,
                        VAR_SAMP(1) AS Variance,
                        STDDEV_SAMP(1) AS StdDev
                    FROM (
                      SELECT DISTINCT Server, Server.Players, Server.Country AS Country
                      FROM ServerPlugin
                      LEFT OUTER JOIN Server ON Server.ID = ServerPlugin.Server
                      WHERE ServerPlugin.Updated >= ?
                    ) dev
                    GROUP BY Country');
$statement->execute(array($minimum));

while ($row = $statement->fetch()) {
    doGeneration(GLOBAL_PLUGIN_ID, $row);
}