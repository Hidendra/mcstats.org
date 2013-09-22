<?php

define('ROOT', '../private_html/');

require_once ROOT . 'config.php';
require_once ROOT . 'includes/database.php';
require_once ROOT . 'includes/func.php';

$baseEpoch = normalizeTime();
$minimum = strtotime('-30 minutes', $baseEpoch);

$master_db_handle->exec('UPDATE Plugin set ServerCount30 = 0');
$statement = $master_db_handle->prepare('UPDATE Plugin dest, (SELECT
            Plugin,
            COUNT(*) AS Count
        FROM ServerPlugin
        LEFT OUTER JOIN Server ON Server.ID = ServerPlugin.Server
        WHERE Updated >= ?
        GROUP BY Plugin) src
SET dest.ServerCount30 = src.Count where dest.ID = src.Plugin');
$statement->execute(array($minimum));