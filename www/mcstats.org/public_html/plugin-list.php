<?php
define('ROOT', './');
session_start();

require_once ROOT . 'config.php';
require_once ROOT . 'includes/database.php';
require_once ROOT . 'includes/func.php';

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
$totalPages = ceil(count(loadPlugins(PLUGIN_ORDER_POPULARITY)) / PLUGIN_LIST_RESULTS_PER_PAGE);

// offset is how many plugins to start after
$offset = ($currentPage - 1) * PLUGIN_LIST_RESULTS_PER_PAGE;

if ($currentPage > $totalPages)
{
    header('Location: /plugin-list/' . $totalPages . '/');
    exit;
}

/// Templating
$page_title = 'MCStats :: Plugin List';
$container_class = 'container-fluid';
send_header();

echo '

            <div class="row-fluid" style="text-align: center; margin-bottom: 15px;">
                    <h2> MCStats / Plugin Metrics </h2>
';

// get last updated
$timelast = getTimeLast();

// display the time since the last graph update
if($timelast > 0) {
    $lastUpdate = floor($timelast / 60);
    $nextUpdate = $config['graph']['interval'] - $lastUpdate;
    if ($nextUpdate < 0)
    {
        $nextUpdate = 0;
    }

    echo '
                    <p> Last update: ' . $lastUpdate . ' minutes ago <br/>
                        Next update in: ' . $nextUpdate . ' minutes</p>
';
}

$output = $cache->get('plugin_list');

if (!$output)
{
    ob_start();

    echo '
            </div>

            <div class="row-fluid">

                <div class="span4" style="width: 300px;">

                    <table class="table table-striped table-bordered table-condensed" id="plugin-list">
                        <thead>
                            <tr> <th style="text-align: center; width: 20px;">Rank <br/> &nbsp; </th> <th style="text-align: center; width: 160px;"> Plugin <br/> &nbsp; </th> <th style="text-align: center; width: 100px;"> Servers<br/> <span style="font-size: 10px;">(last 24 hrs)</span> </th> </tr>
                        </thead>

                        <tbody>
';

    $step = 1;
    foreach (loadPlugins(PLUGIN_ORDER_POPULARITY, PLUGIN_LIST_RESULTS_PER_PAGE, $offset) as $plugin)
    {
        if ($plugin->isHidden()) {
            continue;
        }

        // calculate this plugin's rank
        $rank = $offset + $step;

        $pluginName = htmlentities($plugin->getName());
        $format = number_format($plugin->getServerCount());

        if ($rank <= 10) {
            $rank = '<b>' . $rank . '</b>';
            $pluginName = '<b>' . $pluginName . '</b>';
            $format = '<b>' . $format . '</b>';
        }

        echo '                          <tr id="plugin-list-item"> <td style="text-align: center;">' . $rank . ' </td> <td> <a href="/plugin/' . htmlentities($plugin->getName()) . '" target="_blank">' . $pluginName . '</a> </td> <td style="text-align: center;"> ' . $format . ' </td> </tr>
';
        $step ++;
    }

    echo '                          <tr>
                                    <td style="text-align: center;" id="plugin-list-page-number"> <span id="plugin-list-current-page">' . $currentPage . '</span>/<span id="plugin-list-max-pages">' . $totalPages . '</span> </td>
                                    <td style="text-align: center;"> <a href="#" class="btn btn-mini" id="plugin-list-back" onclick="movePluginListBack()" style="' . ($currentPage == 1 ? 'display: none; ' : '') . 'margin: 0;"><i class="icon-arrow-left"></i> Back</a> <a href="#" class="btn btn-mini" id="plugin-list-forward" onclick="movePluginListForward()" style="' . ($currentPage == $totalPages ? 'display: none; ' : '') . 'margin: 0;">Forward <i class="icon-arrow-right"></i></a> </td>
                                    <td style="text-align: center;"> <input class="input-mini" type="text" value="' . $currentPage . '" id="plugin-list-goto-page" style="height: 12px; margin: 0; width: 20px; text-align: center;" /> <a href="#" class="btn btn-mini" id="plugin-list-go" onclick="loadPluginListPage($(\'#plugin-list-goto-page\').val());">Go <i class="icon-share-alt"></i></a> </td>
                                </tr>

                        </tbody>';

    echo '
                    </table>
                </div>

                <div style="margin-left: 310px;">
';

// Load the global plugin
    $globalPlugin = loadPluginByID(GLOBAL_PLUGIN_ID);
    outputGraphs($globalPlugin);

    echo '          </div>
            </div>';

    $output = ob_get_contents();
    ob_end_clean();
    $cache->set('plugin_list', $output, CACHE_UNTIL_NEXT_GRAPH);
}

echo $output;

/// Templating
send_footer();