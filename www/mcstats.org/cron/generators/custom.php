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
                        ColumnID,
                        SUM(DataPoint) AS Sum,
                        COUNT(DataPoint) AS Count,
                        AVG(DataPoint) AS Avg,
                        MAX(DataPoint) AS Max,
                        MIN(DataPoint) AS Min,
                        VAR_SAMP(DataPoint) AS Variance,
                        STDDEV_SAMP(DataPoint) AS StdDev
                    FROM CustomData WHERE Updated >= ?
                    GROUP BY ColumnID');
$statement->execute(array($minimum));

while ($data = $statement->fetch()) {
    // assign it all
    $plugin = $data['Plugin'];
    $columnID = $data['ColumnID'];
    $sum = $data['Sum'];
    $count = $data['Count'];
    $avg = $data['Avg'];
    $max = $data['Max'];
    $min = $data['Min'];
    $variance = $data['Variance'];
    $stddev = $data['StdDev'];

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
        ':Plugin' => $plugin,
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