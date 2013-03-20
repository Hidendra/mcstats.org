<?php

define('ROOT', '../private_html/');

require_once ROOT . 'config.php';
require_once ROOT . 'includes/database.php';
require_once ROOT . 'includes/func.php';

foreach (loadPlugins(PLUGIN_ORDER_ALPHABETICAL) as $plugin) {
    $plugin->setServerCount($plugin->countServersLastUpdated(normalizeTime() - SECONDS_IN_DAY));
    $plugin->save();

    echo sprintf('%d: %s%s', $plugin->getServerCount(), $plugin->getName(), PHP_EOL);
}