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
    $osname = $data['osname'];
    $osversion = $data['osversion'];
    $sum = $data['Sum'];
    $count = $data['Count'];
    $avg = $data['Avg'];
    $max = $data['Max'];
    $min = $data['Min'];
    $variance = $data['Variance'];
    $stddev = $data['StdDev'];

    $fullName = $osname . '~=~' . $osversion;

    if ($count == 0 || $sum == 0) {
        return;
    }

    if ($osname == '' || $osname == 'Unknown' || $osversion == '' || $osversion == 'Unknown') {
        return;
    }

    $graph = $plugin->getOrCreateGraph('Operating System', false, 0, GraphType::Pie, TRUE, 9010);
    insertGraphDataScratch($graph, $pluginId, $fullName, $baseEpoch, $sum, $count, $avg, $max, $min, $variance, $stddev);
}

// Plugins
$statement = get_slave_db_handle()->prepare('
        SELECT
            Plugin,
            osname,
            osversion,
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
        GROUP BY Plugin, osname, osversion');
$statement->execute(array($minimum));

while ($row = $statement->fetch()) {
    doGeneration($row['Plugin'], $row);
}

// global plugin
$statement = get_slave_db_handle()->prepare('
                    SELECT
                        osname,
                        osversion,
                        SUM(1) AS Sum,
                        COUNT(dev.Server) AS Count,
                        AVG(1) AS Avg,
                        MAX(1) AS Max,
                        MIN(1) AS Min,
                        VAR_SAMP(1) AS Variance,
                        STDDEV_SAMP(1) AS StdDev
                    FROM (
                      SELECT DISTINCT Server, Server.Players, Server.osname AS osname, Server.osversion as osversion
                      FROM ServerPlugin
                      LEFT OUTER JOIN Server ON Server.ID = ServerPlugin.Server
                      WHERE ServerPlugin.Updated >= ?
                    ) dev
                    GROUP BY osname, osversion');
$statement->execute(array($minimum));

while ($row = $statement->fetch()) {
    doGeneration(GLOBAL_PLUGIN_ID, $row);
}