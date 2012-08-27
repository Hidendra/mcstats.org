<?php
define('ROOT', '../../');

require_once ROOT . 'config.php';
require_once ROOT . 'includes/database.php';
require_once ROOT . 'includes/func.php';

header('Last-Modified: ' . gmdate('D, d M Y H:i:s', getLastGraphEpoch()) . ' GMT');

// Fine-tune this or allow customizations?
$hours = 744;

// Our json encoded response
$response = array();

if (!isset($_GET['plugin']))
{
    $response['msg'] = 'No plugin provided';
    $response['status'] = 'err';
    exit(json_encode($response));
}

if (!isset($_GET['graph']))
{
    $response['msg'] = 'No graph name provided';
    $response['status'] = 'err';
    exit(json_encode($response));
}

$plugin = loadPlugin(urldecode($_GET['plugin']));

if ($plugin === NULL)
{
    $response['msg'] = 'Invalid plugin';
    $response['status'] = 'err';
    exit(json_encode($response));
}

// Decide which graph they want
$graphName = urldecode($_GET['graph']);
switch ($graphName)
{
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

        if ($graph == NULL)
        {
            $response['msg'] = 'Invalid graph type';
            $response['status'] = 'err';
            break;
        }

        // if it's a pie chart, it's easier
        if ($graph->getType() == GraphType::Pie)
        {
            $response['status'] = 'ok';
            $response['data'] = DataGenerator::generateCustomChartData($graph, -1, $hours);
            $response['name'] = htmlentities($graph->getName());
            $response['type'] = GraphType::toString($graph->getType());
        }

        // otherwise we need to generate data for every column
        else
        {
            foreach ($graph->getColumns() as $columnID => $columnName)
            {
                if (is_numeric($columnName) || is_double($columnName))
                {
                    $columnName = "\0" . $columnName;
                }

                $response['data'][utf8_encode($columnName)] = DataGenerator::generateCustomChartData($graph, $columnID, $hours);
            }

            $response['status'] = 'ok';
            $response['name'] = htmlentities($graph->getName());
            $response['type'] = GraphType::toString($graph->getType());
        }


        break;

}

echo json_encode($response, JSON_NUMERIC_CHECK);