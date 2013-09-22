<?php
define('ROOT', './');
session_start();

require_once ROOT . '../private_html/config.php';
require_once ROOT . '../private_html/includes/database.php';
require_once ROOT . '../private_html/includes/func.php';
cacheCurrentPage();

/// Templating
$page_title = 'MCStats :: Reports';

if (!isset($_GET['period'])) {
    echo '<p>No period provided.</p>';
    $breadcrumbs = '<a href="#" class="current">Error</a>';
    send_header();
} else {
    $period = $_GET['period'];

    $file = null;
    $name = '';

    switch ($period) {
        case 'december-2012':
            $name = 'December 2012';
            $file = 'december_2012.php';
            $page_title = 'MCStats :: December 2012 Report';
            break;
        case 'january-2013':
            $name = 'January 2013';
            $file = 'january_2013.php';
            $page_title = 'MCStats :: January 2013 Report';
            break;
    }

    if ($file == null) {
        $breadcrumbs = '<a href="#" class="current">Error</a>';
        send_header();
        echo '<p>Invalid period.</p>';
    } else {
        $breadcrumbs = '<a href="/reports/' . $period . '/" class="current">Report: ' . $name . '</a>';
        send_header();
        require ROOT . '../reports/' . $file;
    }
}

send_footer();