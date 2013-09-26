<?php
define('ROOT', './');
session_start();

require_once ROOT . '../private_html/config.php';
require_once ROOT . '../private_html/includes/func.php';
require_once ROOT . '../private_html/includes/database.php';

cacheCurrentPage();

// get the current page
$currentPage = 1;

if (isset($_GET['page'])) {
    $currentPage = intval($_GET['page']);
}

// If the show more link should be shown
$showMoreServers = false;

// number of pages
$totalPages = ceil(countPlugins(PLUGIN_ORDER_POPULARITY) / PLUGIN_LIST_RESULTS_PER_PAGE);

// offset is how many plugins to start after
$offset = ($currentPage - 1) * PLUGIN_LIST_RESULTS_PER_PAGE;

if ($currentPage > $totalPages) {
    header('Location: /plugin-list/' . $totalPages . '/');
    exit;
}

/// Templating
$page_title = 'MCStats :: Global Statistics';
$breadcrumbs = '<a href="/global-stats.php" class="current">Global Statistics</a>';
send_header();

$plugin = loadPluginByID(GLOBAL_PLUGIN_ID);

$servers_graph = array();
$players_graph = array();

//
$global_stats = $plugin->getGraphByName('Global Statistics');
$servers_column = $global_stats->getColumnID('Servers');
$players_column = $global_stats->getColumnID('Players');

$statement = get_slave_db_handle()->prepare('select ColumnID, Sum from GraphData where Plugin = ? AND (ColumnID = ? OR ColumnID = ?) AND Epoch >= ? order by Epoch DESC limit 10');
$cursor = $m_graphdata->find(array(
    'plugin' => intval($plugin->getID()),
    'graph' => intval($global_stats->getID()),
    'epoch' => array(
        '$gte' => intval(time() - SECONDS_IN_DAY * 5)
    )
))->limit(10)->sort(array('epoch' => -1));

foreach ($cursor as $doc) {
    if (isset($doc['data'][$servers_column])) {
        $servers_graph[] = $doc['data'][$servers_column]['sum'];
    }

    if (isset($doc['data'][$players_column])) {
        $players_graph[] = $doc['data'][$players_column]['sum'];
    }
}

$servers_count = count($servers_graph);

if ($servers_count > 2) {
    $servers_class = $servers_graph[0] > $servers_graph[1] ? 'sparkline_line_good' : 'sparkline_line_bad';
    $servers_diff = $servers_graph[0] - $servers_graph[1];
    $players_count = count($players_graph);
    $players_class = $players_graph[0] > $players_graph[1] ? 'sparkline_line_good' : 'sparkline_line_bad';
    $players_diff = $players_graph[0] - $players_graph[1];

    if ($servers_diff > 0) {
        $servers_diff = '+' . $servers_diff;
    }
    if ($players_diff > 0) {
        $players_diff = '+' . $players_diff;
    }

    //
    $current_servers = number_format($servers_graph[0]);
    $current_players = number_format($players_graph[0]);
    $players_graph = array_reverse($players_graph);
    $servers_graph = array_reverse($servers_graph);
    $players_graph = implode(',', $players_graph);
    $servers_graph = implode(',', $servers_graph);
} else {
    $current_players = 'None';
    $current_servers = 'None';
    $servers_diff = '&infin;';
    $players_diff = '&infin;';
    $players_class = '';
    $servers_class = '';
    $players_graph = '';
    $servers_graph = '';
}

echo '

				<div class="row-fluid">
					<div class="span12 center" style="text-align: center; padding-bottom: 0;">
						<ul class="stat-boxes">
							<li>
								<div class="left ' . $servers_class . '"><span>' . $servers_graph . '</span>' . $servers_diff . '</div>
								<div class="right" style="width: 80px">
									<strong>' . $current_servers . '</strong>
									Servers
								</div>
							</li>
							<li>
								<div class="left ' . $players_class . '"><span>' . $players_graph . '</span>' . $players_diff . '</div>
								<div class="right" style="width: 80px">
									<strong>' . $current_players . '</strong>
									Players
								</div>
							</li>
						</ul>
					</div>
				</div>

				<div class="row-fluid">
					<div class="span12 center" style="text-align: center;">
						<ul class="quick-actions" style="margin: 0;">
							<li>
								<a href="http://api.mcstats.org/signature/All+Servers.png" target="_blank">
									<span>Signature image</span>
								</a>
							</li>
							<li>
								<a href="http://api.mcstats.org/plugin-preview/All+Servers.png" target="_blank">
									<span>Signature image (no borders/branding)</span>
								</a>
							</li>
						</ul>
					</div>
				</div>

';

outputGraphs($plugin);

/// Templating
send_footer();