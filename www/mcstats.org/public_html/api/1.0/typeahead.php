<?php
define('ROOT', '../../');
header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');

require_once ROOT . '../private_html/config.php';
require_once ROOT . '../private_html/includes/database.php';
require_once ROOT . '../private_html/includes/func.php';

cacheCurrentPage();

// Our json encoded response
$response = array();

if (!isset($_REQUEST['q'])) {
    $response['msg'] = 'No query provided';
    $response['status'] = 'err';
    exit(json_encode($response));
}

$query = $_REQUEST['q'];

$statement = get_slave_db_handle()->prepare('SELECT Name FROM Plugin WHERE Name LIKE ?');
$statement->execute(array('%' . $query . '%'));

$matchness = array();
while ($row = $statement->fetch()) {
    $name = $row['Name'];
    $matchness[$name] = levenshtein($query, $name);
}

asort($matchness);
$matchness = array_slice($matchness, 0, 10);

foreach ($matchness as $name => $lev) {
    $response[] = $name;
}

echo json_encode($response);