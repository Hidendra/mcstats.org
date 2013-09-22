<?php

define('ROOT', '../private_html/');

require_once ROOT . 'config.php';
require_once ROOT . 'includes/database.php';
require_once ROOT . 'includes/func.php';

/** @var Plugin[] $plugins */
$plugins = array();

foreach (loadPlugins(PLUGIN_ORDER_ALPHABETICAL) as $plugin) {
    $plugins[$plugin->getID()] = $plugin;
}

$baseEpoch = normalizeTime();
$minimum = strtotime('-30 minutes', $baseEpoch);

$statement = get_slave_db_handle()->prepare('
                    SELECT
                        CustomData.Plugin,
                        CustomColumn.Graph as GraphID,
                        ColumnID,
                        SUM(DataPoint) AS Sum,
                        COUNT(DataPoint) AS Count,
                        AVG(DataPoint) AS Avg,
                        MAX(DataPoint) AS Max,
                        MIN(DataPoint) AS Min,
                        VAR_SAMP(DataPoint) AS Variance,
                        STDDEV_SAMP(DataPoint) AS StdDev
                    FROM CustomData
                    LEFT OUTER JOIN CustomColumn ON CustomColumn.ID = ColumnID
                    WHERE CustomData.Updated >= ?
                    GROUP BY ColumnID');
$statement->execute(array($minimum));

$queue = array();
$progress = 0;

while ($data = $statement->fetch()) {
    // assign it all
    $pluginId = $data['Plugin'];
    $graphId = $data['GraphID'];
    $columnId = $data['ColumnID'];
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

    $plugin = $plugins[$pluginId];
    $graph = $plugin->getGraph($graphId);
    $columnName = $graph->getColumns()[$columnId];

    $toset = array();
    if ($sum != 0) $toset['sum'] = intval($sum);
    if ($count != 0) $toset['count'] = intval($count);
    if ($avg != 0) $toset['avg'] = intval($avg);
    if ($max != 0) $toset['max'] = intval($max);
    if ($min != 0) $toset['min'] = intval($min);
    if ($variance != 0) $toset['variance'] = intval($variance);
    if ($stddev != 0) $toset['stddev'] = intval($stddev);

    $k = $pluginId . '/' . $graphId;
    if (isset($queue[$k])) {
        $queue[$k]['data'][$columnId] = $toset;
    } else {
        $queue[$k] = array(
            'epoch' => $baseEpoch,
            'plugin' => $pluginId,
            'graph' => $graphId,
            'column' => $columnId,
            'data' => array(
                $columnId => $toset
            )
        );
    }

    if ($progress % 1000 == 0) {
        echo 'Read: ' . $progress . PHP_EOL;
    }

    $progress++;
    // insertGraphData($graph, $pluginId, $columnName, $baseEpoch, $sum, $count, $avg, $max, $min, $variance, $stddev);
}

$progress = 0;
$queueSize = count($queue);

foreach ($queue as $k => $gdata) {
    $toset = array();

    foreach ($gdata['data'] as $g_column => $g_data) {
        $toset['data.' . $g_column] = $g_data;
    }

    $m_graphdata->update(array(
        'epoch' => intval($gdata['epoch']),
        'plugin' => intval($gdata['plugin']),
        'graph' => intval($gdata['graph'])
    ), array(
        '$set' => $toset
    ), array(
        'upsert' => true
    ));

    if ($progress % 100 == 0) {
        echo 'Flushed: ' . $progress . '/' . $queueSize . PHP_EOL;
    }

    $progress++;
}