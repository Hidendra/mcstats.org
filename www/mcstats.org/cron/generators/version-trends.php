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
                        VersionHistory.Version as VersionID,
                        (SELECT Version FROM Versions where ID = VersionHistory.Version) as Name,
                        SUM(1) AS Sum,
                        COUNT(VersionHistory.Version) AS Count,
                        AVG(1) AS Avg,
                        MAX(1) AS Max,
                        MIN(1) AS Min,
                        VAR_SAMP(1) AS Variance,
                        STDDEV_SAMP(1) AS StdDev
                    FROM VersionHistory
                    WHERE VersionHistory.Created >= ?
                    GROUP BY Plugin, Name');
$statement->execute(array($minimum));

while ($data = $statement->fetch()) {
    $pluginId = $data['Plugin'];
    $plugin = loadPluginByID($pluginId);
    $version = $data['Name'];
    $sum = $data['Sum'];
    $count = $data['Count'];
    $avg = $data['Avg'];
    $max = $data['Max'];
    $min = $data['Min'];
    $variance = $data['Variance'];
    $stddev = $data['StdDev'];

    if ($pluginId == -1) {
        continue;
    }

    $graph = $plugin->getOrCreateGraph('Version Trends', false, 1, GraphType::Area, TRUE, 9003);
    $columnID = $graph->getColumnID($version);

    // these can be NULL IFF there is only one data point (e.g one server) in the sample
    // we're using sample functions NOT population so this should be fairly obvious why
    // this will return null
    if ($variance === null || $stddev === null) {
        $variance = 0;
        $stddev = 0;
    }

    // insert it into the database
    $insert = $master_db_handle->prepare('INSERT INTO GraphDataScratch (Plugin, ColumnID, Sum, Count, Avg, Max, Min, Variance, StdDev, Epoch)
                                                    VALUES (:Plugin, :ColumnID, :Sum, :Count, :Avg, :Max, :Min, :Variance, :StdDev, :Epoch)');
    $insert->execute(array(
        ':Plugin' => $pluginId,
        ':ColumnID' => $columnID,
        ':Epoch' => $baseEpoch,
        ':Sum' => $sum,
        ':Count' => $count,
        ':Avg' => $avg,
        ':Max' => $max,
        ':Min' => $min,
        ':Variance' => $variance,
        ':StdDev' => $stddev
    ));
}