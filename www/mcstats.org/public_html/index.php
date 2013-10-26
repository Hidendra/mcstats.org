<?php
define('ROOT', './');
session_start();

require_once ROOT . '../private_html/config.php';
require_once ROOT . '../private_html/includes/database.php';
require_once ROOT . '../private_html/includes/func.php';

/// Templating
$page_title = 'MCStats :: Homepage';
$container_class = 'container';
send_header();

$plugin = loadPluginByID(GLOBAL_PLUGIN_ID);
$graph = $plugin->getOrCreateGraph('Global Statistics');

$serverCount = number_format($plugin->getTimelineCustomLast($graph->getColumnID('Servers'), $graph));
$playerCount = number_format($plugin->getTimelineCustomLast($graph->getColumnID('Players'), $graph));

$statement = get_slave_db_handle()->prepare('SELECT COUNT(*) FROM Plugin where LastUpdated >= ?');
$statement->execute(array(normalizeTime() - SECONDS_IN_DAY));

$row = $statement->fetch();

$pluginCount = $row ? $row[0] : 0;

echo <<<END

<script type="text/javascript">
    $(document).ready(function() {
        $("#players-popover").popover();
    });
</script>


<div class="row-fluid">
    <div class="col-xs-12">
        <h1 style="margin-bottom:10px; font-size:57px;">Glorious plugin stats!</h1>
        <p>MCStats / Plugin Metrics is the de facto statistical engine for Minecraft, actively used by over <b>$pluginCount</b> plugins.</p>
        <p>Across the world, over <b>$playerCount</b> players have been seen <b>in the last 30 minutes</b> on over <b>$serverCount</b> servers.</p>
        <p><a href="/learn-more/" class="btn btn-success"><i class="icon-white icon-star-empty"></i> Learn More</a>  <a class="btn btn-primary" href="/plugin-list/"><i class="icon-white icon-th-list"></i> Plugin List</a></p>
    </div>
</div>

<div class="row-fluid" style="text-align: center;">
    <h1 style="margin-bottom:30px; font-size:40px;">4 of the top 100 plugins. Do you use them?</h1>
</div>

END;

$first = true;
foreach (loadPlugins(PLUGIN_ORDER_RANDOM_TOP100, 4) as $plugin) {
    $name = htmlentities($plugin->getName());
    $encodedName = urlencode($name);

    if ($first) {
        echo '<div class="row-fluid">';
    }

    echo <<<END
    <div class="col-xs-6">
        <div class="widget-box">
            <div class="widget-title">
                                <span class="icon">
                                    <i class="icon-signal"></i>
                                    #{$plugin->getRank()}
                                </span>
                <h5><a href="/plugin/$encodedName">$name</a></h5>
            </div>
            <div class="widget-content" style="text-align: center;">
                <img src="http://api.mcstats.org/plugin-preview/2.0/$encodedName.png" alt="$name" width="100%" />
            </div>
        </div>
    </div>
END;

    if (!$first) {
        echo '</div>';
        $first = true;
    } else {
        $first = false;
    }
}

if (!$first) {
    echo '</div>';
}
echo '<div class="clearfix"></div>';

send_footer();