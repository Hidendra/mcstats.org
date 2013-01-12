<?php

/// Todo fully remove other deprecated methods
/// Todo for the old way of generating graphs :D

define('ROOT', './');
session_start();

require_once ROOT . '../private_html/config.php';
require_once ROOT . '../private_html/includes/database.php';
require_once ROOT . '../private_html/includes/func.php';

// Cache until the next interval
header('Cache-Control: public, s-maxage=' . (timeUntilNextGraph() - time()));

if (!isset($_GET['plugin']))
{
    exit('ERR No plugin provided.');
}

// Load the plugin
$plugin = loadPlugin($_GET['plugin']);

// Doesn't exist
if ($plugin === NULL)
{
    exit('ERR Invalid plugin.');
}

// Get the plugin name
$pluginName = htmlentities($plugin->getName());
$encodedName = urlencode($pluginName); // encoded name, for use in signature url

/// Template hook
$page_title = 'MCStats :: ' . $pluginName;
$breadcrumbs = '<a href="/plugin/' . $encodedName . '" class="current">Plugin: ' . $pluginName . '</a>';
$sidebar_more = '
                <li class="submenu active open">
                    <a href="#"><i class="icon icon-star"></i> <span>Plugin: <strong>' . $pluginName . '</strong></span></a>
                    <ul>
                        <li><a>Added on: <strong>' . date('F d, Y', $plugin->getCreated()) . '</strong></a></li>
                        <li><a>Rank held for: <strong>' . epochToHumanString(time() - $plugin->getLastRankChange(), FALSE) . '</strong></a></li>
                        <li><a>Global starts: <strong>' . number_format($plugin->getGlobalHits()) . '</strong></a></li>
                    </ul>
                </li>

';

/// Templating
send_header();

$output = $cache->get('plugin/' . $plugin->getName());

if (!$output)
{
    ob_start();

    $rank = $plugin->getRank();
    $rank_class = '';
    $rank_change = '';
    $rank_graph = '';
    if ($rank == '')
    {
        $rank = '<i>Not ranked</i>';
    } else
    {
        // bolden the rank if they're in the top-10
        if (is_numeric($rank) && $rank <= 10)
        {
            $rank = '<b>' . $rank . '</b>';
        }

        // increase
        if ($plugin->getRank() < $plugin->getLastRank())
        {
            $rank_class = 'peity_bar_good';
            $rank_change = '+' . ($plugin->getLastRank() - $plugin->getRank());
            $rank_graph = '1,2,3,4,5,6,7,8';
        }

        // decrease
        elseif ($plugin->getRank() > $plugin->getLastRank())
        {
            $rank_class = 'peity_bar_bad';
            $rank_change = '-' . ($plugin->getRank() - $plugin->getLastRank());
            $rank_graph = '8,7,6,5,4,3,2,1';
        }

        // no change
        else
        {
            $rank_class = 'peity_line_neutral';
            $rank_change = '&plusmn;0';
            $rank_graph = '4,4,4,4,4,4,4,4';
        }
    }

    $servers_graph = '';
    $players_graph = '';

    //
    $global_stats = $plugin->getGraphByName('Global Statistics');
    $servers_column = $global_stats->getColumnID('Servers');
    $players_column = $global_stats->getColumnID('Players');

    $statement = get_slave_db_handle()->prepare('select ColumnID, Sum from GraphData where Plugin = ? AND (ColumnID = ? OR ColumnID = ?) AND Epoch >= ? order by Epoch DESC limit 10');

    //
    $statement->execute(array($plugin->getID(), $servers_column, $players_column, time() - SECONDS_IN_DAY));
    while ($row = $statement->fetch()) {
        if ($servers_column == $row['ColumnID']) {
            $servers_graph[] = $row['Sum'];
        } else {
            $players_graph[] = $row['Sum'];
        }
    }

    $servers_count = count($servers_graph);

    if ($servers_count > 2) {
        $servers_class = $servers_graph[0] > $servers_graph[1] ? 'peity_line_good' : 'peity_line_bad';
        $servers_diff = $servers_graph[0] - $servers_graph[1];
        $players_count = count($players_graph);
        $players_class = $players_graph[0] > $players_graph[1] ? 'peity_line_good' : 'peity_line_bad';
        $players_diff = $players_graph[0] - $players_graph[1];

        if ($servers_diff > 0) $servers_diff = '+' . $servers_diff;
        if ($players_diff > 0) $players_diff = '+' . $players_diff;

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
            <script>
                // Plugin-specific bindings
                var pluginName = "' . $pluginName . '";
            </script>

				<div class="row-fluid">
					<div class="span12 center" style="text-align: center; padding-bottom: 0;">
						<ul class="stat-boxes">
							<li>
								<div class="left ' . $rank_class . '" title="Not currently real data, it just shows the direction :-)"><span>' . $rank_graph . '</span>' . $rank_change . '</div>
								<div class="right">
									<strong>' . $rank . '</strong>
									Rank
								</div>
							</li>
							<li>
								<div class="left ' . $servers_class . '"><span>' . $servers_graph . '</span>' . $servers_diff . '</div>
								<div class="right">
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
								<a href="/signature/' . $encodedName . '.png" target="_blank">
									<span>Signature image</span>
								</a>
							</li>
							<li>
								<a href="/plugin-preview/' . $encodedName . '.png" target="_blank">
									<span>Signature image (no borders/branding)</span>
								</a>
							</li>
						</ul>
					</div>
				</div>

            <div id="row-fluid" style="width: 100%; float: left;">

                <div id="graph-content">
';

    outputGraphs($plugin);

    echo '
                </div>
                <div class="clear"></div>';
    $output = ob_get_contents();
    ob_end_clean();
    $cache->set('plugin/' . $plugin->getName(), $output, CACHE_UNTIL_NEXT_GRAPH);
}

echo $output;

/// Templating
send_footer();