<?php

define ('HOURS', 168);

define ('ROOT', './');
require_once ROOT . 'config.php';
require_once ROOT . 'includes/database.php';
require_once ROOT . 'includes/func.php';

// set the search path for fonts
putenv('GDFONTPATH=' . realpath('../fonts/'));

// The image's height
define('IMAGE_HEIGHT', 124);

// The image's width
define('IMAGE_WIDTH', 478);

// We will be outputting a PNG image!
header ('Content-type: image/png');

if (!isset($_GET['plugin']))
{
    error_image('Error: No plugin provided');
}

// Required requirements
require 'pChart/pData.class.php';
require 'pChart/pChart.class.php';
require 'pChart/pCache.class.php';

// The plugin we are graphing
$pluginName = urldecode($_GET['plugin']);

// Load the json data from the api
// First, basic plugin data
$plugin = loadPlugin($pluginName);

// Is the plugin invalid?
if ($plugin == null)
{
    // no plugin found
    error_image('Invalid plugin');
}

// case-correct plugin name
$pluginName = $plugin->getName();

// Create a new data set
$dataSet = new pData();

// The servers plot
$serversX = array();

// The players plot
$playersX = array();
$graph_data = array(); // epoch => [ "servers" => v, "players" => v ]

// load the plugin's stats graph
$globalstatistics = $plugin->getOrCreateGraph('Global Statistics');
// the player plot's column id
$playersColumnID = $globalstatistics->getColumnID('Players');
// server plot's column id
$serversColumnID = $globalstatistics->getColumnID('Servers');

foreach (DataGenerator::generateCustomChartData($globalstatistics, $playersColumnID, HOURS) as $data)
{
    $epoch = $data[0];
    $value = $data[1];

    $graph_data[$epoch]['players'] = $value;
}

foreach (DataGenerator::generateCustomChartData($globalstatistics, $serversColumnID, HOURS) as $data)
{
    $epoch = $data[0];
    $value = $data[1];

    $graph_data[$epoch]['servers'] = $value;
}

foreach ($graph_data as $epoch => $data)
{
    // Ignore missing data
    if (count($data) != 2)
    {
        continue;
    }

    // Add it
    $playersX[] = $data['players'];
    $serversX[] = $data['servers'];
}

// Free up some memory
unset($graph_data);

// Add the data to the graph
$dataSet->AddPoint($playersX, 'Serie1');
$dataSet->AddPoint($serversX, 'Serie2');

// Create the series
$dataSet->AddSerie('Serie1');
$dataSet->AddSerie('Serie2');
$dataSet->SetSerieName('Players', 'Serie1');
$dataSet->SetSerieName('Servers', 'Serie2');
$dataSet->SetYAxisName('');

// Add all of the series
$dataSet->AddAllSeries();

// Set us up the bomb
$graph = new pChart(IMAGE_WIDTH, IMAGE_HEIGHT);
$graph->setFontProperties('tahoma.ttf', 8);
$graph->setGraphArea(60, 30, IMAGE_WIDTH - 20, IMAGE_HEIGHT - 30);
$graph->drawFilledRoundedRectangle(7, 7, IMAGE_WIDTH - 7, IMAGE_HEIGHT - 7, 5, 240, 240, 240);
$graph->drawRoundedRectangle(5, 5, IMAGE_WIDTH - 5, IMAGE_HEIGHT - 5, 5, 230, 230, 230);
$graph->drawGraphArea(250, 250, 250, true);
$graph->drawScale($dataSet->GetData(), $dataSet->GetDataDescription(), SCALE_START0, 150, 150, 150, true, 0, 0);
// $graph->drawGrid(4, true, 230, 230, 230, 100);

if ($plugin->getID() == GLOBAL_PLUGIN_ID)
{
    $statement = get_slave_db_handle()->prepare('SELECT Sum(GlobalHits) FROM Plugin');
    $statement->execute();

    $serverStarts = $statement->fetch()[0];
    $serversLast24Hours = 0;
} else
{
    $serverStarts = $plugin->getGlobalHits();
    $serversLast24Hours = $plugin->countServersLastUpdated(time() - SECONDS_IN_DAY);
}

// Draw the footer
$graph->setFontProperties('pf_arma_five.ttf', 6);
$footer = sprintf('%s servers in the last 24 hours with %s all-time server startups  ', number_format($serversLast24Hours), number_format($serverStarts));
$graph->drawTextBox(60, IMAGE_HEIGHT - 25, IMAGE_WIDTH - 20, IMAGE_HEIGHT - 7, $footer, 0, 255, 255, 255, ALIGN_RIGHT, true, 0, 0, 0, 30);

// Draw the data
$graph->drawFilledLineGraph($dataSet->GetData(), $dataSet->GetDataDescription(), 75, true);

// Draw legend
$graph->drawLegend(65, 35, $dataSet->GetDataDescription(), 255, 255, 255);

// Get the center of the image
$authors = $plugin->getAuthors();
if (!empty($authors))
    $title = $pluginName . ' - ' . $authors;
else
    $title = $pluginName;

$tahoma = 'tahoma.ttf';
$bounding_box = imagettfbbox(11, 0, $tahoma, $title);
$center_x = ceil((IMAGE_WIDTH - $bounding_box[2]) / 2);

// Draw the title there
$graph->setFontProperties($tahoma, 11); // Switch to font size 10
$graph->drawTitle($center_x, 22, $title, 50, 50, 50);

// shameless advertising
$graph->setFontProperties('pf_arma_five.ttf', 6);
$graph->drawTitle(63, IMAGE_HEIGHT - 9, 'mcstats.org', 210, 210, 210, -1, -1, TRUE);

// Stroke the image
$graphImage = $graph->Render('__handle');

// generate the image
$image = imagecreatetruecolor(IMAGE_WIDTH, IMAGE_HEIGHT);

// Some colors
$white = imagecolorallocate($image, 255, 255, 255);
$black = imagecolorallocate($image, 0, 0, 0);

// Make white transparent
imagecolortransparent($image, $white);

// Fill the background with white
imagefilledrectangle($image, 0, 0, IMAGE_WIDTH, IMAGE_HEIGHT, $white);

// Copy our graph into the image
imagecopy($image, $graphImage, 0, 0, 0, 0, IMAGE_WIDTH, IMAGE_HEIGHT);

imagepng($image);

// Destroy it
imagedestroy($image);

/**
 * Create an error image, send it to the client, and then exit
 *
 * @param $text
 */
function error_image($text)
{
    // allocate image
    $image = imagecreatetruecolor(IMAGE_WIDTH, IMAGE_HEIGHT);

    // create some colours
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);

    // draw teh background
    imagefilledrectangle($image, 0, 0, IMAGE_WIDTH, IMAGE_HEIGHT, $white);

    // write the text
    imagettftext($image, 16, 0, 5, 25, $black, '../fonts/pf_arma_five.ttf', $text);

    // render and destroy the image
    imagepng($image);
    imagedestroy($image);
    exit;
}