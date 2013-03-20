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
$page_title = 'MCStats :: Plugin List';
$breadcrumbs = '<a href="/plugin-list/" class="current">Plugin List</a>';
send_header();

$output = $cache->get('plugin_list');

if (!$output) {
    ob_start();

    echo '

            <div class="row-fluid">

                <div class="span12 widget-content nopaddings" style="padding-left: 0;">

                    <table class="table table-bordered table-condensed data-table" id="plugin-list">
                        <thead>
                            <tr> <th style="text-align: center; width: 15%;">Rank</th> <th style="text-align: center; width: 170px;">Plugin</th> <th style="text-align: center; width: 70px;">Servers</th> </tr>
                        </thead>

                        <tbody>
';

    $step = 1;
    foreach (loadPlugins(PLUGIN_ORDER_POPULARITY, PLUGIN_LIST_RESULTS_PER_PAGE, $offset) as $plugin) {
        if ($plugin->isHidden()) {
            continue;
        }

        $rank = $plugin->getRank();

        $pluginName = htmlentities($plugin->getName());
        $format = number_format($plugin->getServerCount());

        if ($rank <= 10) {
            $rank = '<b>' . $rank . '</b>';
            $pluginName = '<b>' . $pluginName . '</b>';
            $format = '<b>' . $format . '</b>';
        }

        // increase
        if ($plugin->getRank() < $plugin->getLastRank()) {
            $rank .= ' <i class="fam-arrow-up" title="Increased from ' . $plugin->getLastRank() . ' (+' . ($plugin->getLastRank() - $plugin->getRank()) . ')"></i>';
        } // decrease
        elseif ($plugin->getRank() > $plugin->getLastRank()) {
            $rank .= ' <i class="fam-arrow-down" title="Decreased from ' . $plugin->getLastRank() . ' (-' . ($plugin->getRank() - $plugin->getLastRank()) . ')"></i>';
        } // no change
        else {
            $rank .= ' <i class="fam-bullet-blue" title="No change"></i>';
        }

        echo '                          <tr id="plugin-list-item"> <td style="text-align: center;">' . $rank . ' </td> <td> <a href="/plugin/' . htmlentities($plugin->getName()) . '" target="_blank">' . $pluginName . '</a> </td> <td style="text-align: center;"> ' . $format . ' </td> </tr>
';
        $step++;
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
';

    echo '  </div>';

    $output = ob_get_contents();
    ob_end_clean();
    $cache->set('plugin_list', $output, CACHE_UNTIL_NEXT_GRAPH);
}

echo $output;

/// Templating
send_footer();