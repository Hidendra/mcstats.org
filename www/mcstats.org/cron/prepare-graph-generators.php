<?php

define('ROOT', '../private_html/');
define('MAX_COLUMNS', 50); // soft limit of max amount of columns to loop through per plugin
define('MAX_CHILDREN', 30); // the maximum amount of children that can be started

require_once ROOT . 'config.php';
require_once ROOT . 'includes/database.php';
require_once ROOT . 'includes/func.php';

// pre purge official pie/donut graphs
$statement = get_slave_db_handle()->prepare('select * from Graph where (Type = 3 or Type = 6) and (Position >= 1000)');
$statement->execute();

while ($row = $statement->fetch()) {
    $m_graphdata->remove(array(
        'plugin' => intval($row['Plugin']),
        'graph' => intval($row['ID'])
    ));
}

// update latest epoch
$m_statistic->update(array(
    '_id' => 1
), array(
    '$set' => array(
        'max.epoch' => intval(normalizeTime())
    )
), array(
    'upsert' => true
));