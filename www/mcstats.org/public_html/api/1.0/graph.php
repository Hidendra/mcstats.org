<?php
define('ROOT', '../../');
header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');
session_start();

require_once ROOT . '../private_html/config.php';
require_once ROOT . '../private_html/includes/database.php';
require_once ROOT . '../private_html/includes/func.php';

cacheCurrentPage();

// Fine-tune this or allow customizations?
$hours = 744;

// Our json encoded response
$response = array();

if (!isset($_GET['plugin'])) {
    $response['msg'] = 'No plugin provided';
    $response['status'] = 'err';
    exit(json_encode($response));
}

if (!isset($_GET['graph'])) {
    $response['msg'] = 'No graph name provided';
    $response['status'] = 'err';
    exit(json_encode($response));
}

$plugin = loadPlugin(urldecode($_GET['plugin']));

if ($plugin === null) {
    $response['msg'] = 'Invalid plugin';
    $response['status'] = 'err';
    exit(json_encode($response));
}

// Decide which graph they want
$graphName = urldecode($_GET['graph']);
switch ($graphName) {
    case 'global':
        // load the plugin's stats graph
        $globalstatistics = $plugin->getOrCreateGraph('Global Statistics');
        // the player plot's column id
        $playersColumnID = $globalstatistics->getColumnID('Players');
        // server plot's column id
        $serversColumnID = $globalstatistics->getColumnID('Servers');

        $response['status'] = 'ok';
        $response['data']['players'] = DataGenerator::generateCustomChartData($globalstatistics, $playersColumnID, $hours);
        $response['data']['servers'] = DataGenerator::generateCustomChartData($globalstatistics, $serversColumnID, $hours);
        $response['name'] = htmlentities($globalstatistics->getName());
        $response['type'] = GraphType::toString($globalstatistics->getType());
        break;

    case 'players':
        // load the plugin's stats graph
        $globalstatistics = $plugin->getOrCreateGraph('Global Statistics');
        // the player plot's column id
        $playersColumnID = $globalstatistics->getColumnID('Players');

        $response['status'] = 'ok';
        $response['data'] = DataGenerator::generateCustomChartData($globalstatistics, $playersColumnID, $hours);
        $response['name'] = htmlentities($globalstatistics->getName());
        $response['type'] = GraphType::toString($globalstatistics->getType());
        break;

    case 'servers':
        // load the plugin's stats graph
        $globalstatistics = $plugin->getOrCreateGraph('Global Statistics');
        // server plot's column id
        $serversColumnID = $globalstatistics->getColumnID('Servers');

        $response['status'] = 'ok';
        $response['data'] = DataGenerator::generateCustomChartData($globalstatistics, $serversColumnID, $hours);
        $response['name'] = htmlentities($globalstatistics->getName());
        $response['type'] = GraphType::toString($globalstatistics->getType());
        break;

    default:
        $graph = $plugin->getGraphByName($graphName);

        if ($graph == null) {
            $response['msg'] = 'Invalid graph type';
            $response['status'] = 'err';
            break;
        }

        // if it's a pie chart, it's easier
        if ($graph->getType() == GraphType::Pie || $graph->getType() == GraphType::Donut) {
            $response['status'] = 'ok';
            $response['data'] = DataGenerator::generateCustomChartData($graph, -1, $hours);
            $response['name'] = htmlentities($graph->getName());
            $response['type'] = GraphType::toString($graph->getType());
        } else {
            if ($graph->getType() == GraphType::Map) {
                $response['status'] = 'ok';
                $response['data'] = DataGenerator::generateGeoChartData($plugin);
                $response['name'] = htmlentities($graph->getName());
                $response['type'] = GraphType::toString($graph->getType());
            } // otherwise we need to generate data for every column
            else {
                foreach ($graph->getColumns() as $columnID => $columnName) {
                    if (is_numeric($columnName) || is_double($columnName)) {
                        $columnName = "\0" . $columnName;
                    }

                    $response['data'][utf8_encode($columnName)] = DataGenerator::generateCustomChartData($graph, $columnID, $hours);
                }

                // total the counts
                $total = 0;
                foreach ($response['data'] as $name => $data) {
                    $count = count($data);

                    // evict the column if it has none (wasting space !)
                    if ($count == 0) {
                        unset($response['data'][$name]);
                        continue;
                    }

                    $total += $data[$count - 1][1]; // [[0,5], [1,7]] the expression will return 7
                }

                // now evict more data if necessary
                if ($total > 5000 && count($response['data']) > 20) // TODO better magic numbers
                {
                    $removed_total = 0;

                    foreach ($response['data'] as $name => $data) {
                        $count = count($data);
                        $value = $data[$count - 1][1];
                        $percent = ($value / $total) * 100;

                        // evict any data below 0.05%
                        if ($percent <= 0.10) {
                            unset($response['data'][$name]);
                        }
                    }
                }


                $response['status'] = 'ok';
                $response['name'] = htmlentities($graph->getName());
                $response['type'] = GraphType::toString($graph->getType());
            }
        }


        break;

}

echo json_encode($response, JSON_NUMERIC_CHECK);