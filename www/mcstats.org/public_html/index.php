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

$lastEpoch = getLastGraphEpoch();
$statement = get_slave_db_handle()->prepare('SELECT Sum FROM GraphData where ColumnID = ? AND Epoch = ?');

// vars used later on
$pluginCount = 0;

$graph = loadPluginByID(GLOBAL_PLUGIN_ID)->getOrCreateGraph('Global Statistics');

$statement->execute(array($graph->getColumnID('Servers'), $lastEpoch));
$serverCount = number_format($statement->fetch()[0]);
$statement->execute(array($graph->getColumnID('Players'), $lastEpoch));
$playerCount = number_format($statement->fetch()[0]);

$statement = get_slave_db_handle()->prepare('SELECT * FROM Plugin where LastUpdated >= ?');
$statement->execute(array(normalizeTime() - SECONDS_IN_DAY));

$pluginCount = 0;
while ($row = $statement->fetch()) {
    $pluginCount++;
}

// generally the time player count is is last 30-60 minutes, so get the real time for popover
$realTimeUsed = floor((time() - strtotime('-30 minutes', normalizeTime())) / 60);

echo <<<END

<script type="text/javascript">
    $(document).ready(function() {
        $("#players-popover").popover();
    });
</script>


<div class="row-fluid">
    <div class="hero-unit">
        <h1 style="margin-bottom:10px; font-size:57px;">Glorious plugin stats.</h1>
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
    <div class="span6">
        <div class="widget-box">
            <div class="widget-title">
                                <span class="icon">
                                    <i class="icon-signal"></i>
                                    #{$plugin->getRank()}
                                </span>
                <h5><a href="/plugin/$encodedName">$name</a></h5>
            </div>
            <div class="widget-content" style="text-align: center;">
                <img src="/plugin-preview/1.5/$encodedName.png" alt="$name" />
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

send_footer();