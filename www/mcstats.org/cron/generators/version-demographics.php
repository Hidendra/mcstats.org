<?php

define('ROOT', '../private_html/');

require_once ROOT . 'config.php';
require_once ROOT . 'includes/database.php';
require_once ROOT . 'includes/func.php';

$baseEpoch = normalizeTime();
$minimum = strtotime('-30 minutes', $baseEpoch);

$statement = get_slave_db_handle()->prepare('
                    SELECT
                        Plugin,
                        ServerPlugin.Version AS Version,
                        SUM(1) AS Sum,
                        COUNT(*) AS Count,
                        AVG(1) AS Avg,
                        MAX(1) AS Max,
                        MIN(1) AS Min,
                        VAR_SAMP(1) AS Variance,
                        STDDEV_SAMP(1) AS StdDev
                    FROM ServerPlugin WHERE Updated >= ?
                    GROUP BY Plugin, ServerPlugin.Version');
$statement->execute(array($minimum));

while ($data = $statement->fetch()) {
    $pluginId = $data['Plugin'];
    $plugin = loadPluginByID($pluginId);
    $version = $data['Version'];
    $sum = $data['Sum'];
    $count = $data['Count'];
    $avg = $data['Avg'];
    $max = $data['Max'];
    $min = $data['Min'];
    $variance = $data['Variance'];
    $stddev = $data['StdDev'];

    $graph = $plugin->getOrCreateGraph('Version Demographics', false, 1, GraphType::Percentage_Area, true, 9004);
    $columnID = $graph->getColumnID($version);

    // these can be NULL IFF there is only one data point (e.g one server) in the sample
    // we're using sample functions NOT population so this should be fairly obvious why
    // this will return null
    if ($variance === null || $stddev === null) {
        $variance = 0;
        $stddev = 0;
    }

    insertGraphData($graph, $pluginId, $version, $baseEpoch, $sum, $count, $avg, $max, $min, $variance, $stddev);
}