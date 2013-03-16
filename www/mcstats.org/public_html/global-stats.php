<?php
define('ROOT', './');
session_start();

require_once ROOT . '../private_html/config.php';
require_once ROOT . '../private_html/includes/database.php';
require_once ROOT . '../private_html/includes/func.php';

// Cache until the next interval
header('Cache-Control: public, s-maxage=' . (timeUntilNextGraph() - time()));

// get the current page
$currentPage = 1;

if (isset($_GET['page']))
{
    $currentPage = intval($_GET['page']);
}

// If the show more link should be shown
$showMoreServers = false;

// number of pages
$totalPages = ceil(countPlugins(PLUGIN_ORDER_POPULARITY) / PLUGIN_LIST_RESULTS_PER_PAGE);

// offset is how many plugins to start after
$offset = ($currentPage - 1) * PLUGIN_LIST_RESULTS_PER_PAGE;

if ($currentPage > $totalPages)
{
    header('Location: /plugin-list/' . $totalPages . '/');
    exit;
}

/// Templating
$page_title = 'MCStats :: Global Statistics';
$breadcrumbs = '<a href="/global-stats.php" class="current">Global Statistics</a>';
send_header();

$output = $cache->get('global_stats');

if (!$output)
{
    ob_start();

    // Load the global plugin
    $globalPlugin = loadPluginByID(GLOBAL_PLUGIN_ID);
    outputGraphs($globalPlugin);

    $output = ob_get_contents();
    ob_end_clean();
    $cache->set('global_stats', $output, CACHE_UNTIL_NEXT_GRAPH);
}

echo $output;

/// Templating
send_footer();