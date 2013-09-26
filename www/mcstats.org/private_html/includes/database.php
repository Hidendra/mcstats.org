<?php
if (!defined('ROOT')) {
    exit('For science.');
}

/**
 * Global PDO object that is accessible after the database is connected to
 * @var PDO
 */
$master_db_handle = null;

/**
 * The handle to the slave database
 */
$slave_db_handle = null;

/**
 * Get the slave database handle if it is connected, otherwise get the master handle.
 * This is mainly used for SELECT queries that can be offloaded to the slave server
 * if it is enabled.
 *
 * @return PDO
 */
function get_slave_db_handle() {
    global $master_db_handle, $slave_db_handle;
    return $slave_db_handle !== null ? $slave_db_handle : $master_db_handle;
}

/**
 * Attempt to connect to the database
 *
 * @param string database type to connect to in the config, master or slave
 * @return PDO object if connected, otherwise the error message is sent to the error log and exited
 */
function try_connect_database($dbtype = 'master') {
    global $config;
    $db = $config['database'][$dbtype];

    try {
        // Profiling:
        // return new PDOProfiler("mysql:host={$db['hostname']};dbname={$db['dbname']}", $db['username'], $db['password']);
        return new PDO("mysql:host={$db['hostname']};dbname={$db['dbname']}", $db['username'], $db['password'], array(
            PDO::ATTR_PERSISTENT => true
        ));
    } catch (PDOException $e) {
        error_log('Error while connecting to the database ' . $dbtype . ': <br/><b>' . $e->getMessage() . '</b>');
        exit('An error occurred while connecting to the database (' . $dbtype . '). This has been logged.');
    }
}

// Mongo database handle
$mongo_handle = new Mongo('mongodb://10.10.1.60:27017');
$mongo = $mongo_handle->selectDB('mcstats');
$m_graphdata = new MongoCollection($mongo, 'graphdata');
$m_statistic = new MongoCollection($mongo, 'statistic');

$cursor = $m_statistic->find(array('_id' => 1));
$statistic = $cursor->getNext();

// Attempt to connect to the master database
$master_db_handle = try_connect_database();

// Only connect to the slave database if it is enabled
if ($config['database']['slave']['enabled'] == true) {
    $slave_db_handle = try_connect_database('slave');
}