<?php
define('ROOT', './');
session_start();

require_once ROOT . '../private_html/config.php';
require_once ROOT . '../private_html/includes/func.php';

$graphPercent = graph_generator_percentage();

echo $graphPercent == null ? 0 : $graphPercent;