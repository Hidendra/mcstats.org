<?php
define('ROOT', './');
session_start();

require_once ROOT . 'config.php';
require_once ROOT . 'includes/database.php';
require_once ROOT . 'includes/func.php';

/// Templating
$page_title = 'MCStats :: Homepage';
$container_class = 'container';
send_header();

$lastEpoch = getLastGraphEpoch();
$statement = get_slave_db_handle()->prepare('SELECT Sum FROM CustomDataTimeline where ColumnID = ? AND Epoch = ?');

// vars used later on
$pluginCount = 0;

$graph = loadPluginByID(GLOBAL_PLUGIN_ID)->getOrCreateGraph('Global Statistics');

$statement->execute(array($graph->getColumnID('Servers'), $lastEpoch));
$serverCount = number_format($statement->fetch()[0]);
$statement->execute(array($graph->getColumnID('Players'), $lastEpoch));
$playerCount = number_format($statement->fetch()[0]);

$statement = get_slave_db_handle()->prepare('SELECT COUNT(*) FROM Plugin where LastUpdated >= ?');
$statement->execute(array(normalizeTime() - SECONDS_IN_DAY));

if ($row = $statement->fetch())
{
    $pluginCount = $row[0];
}

// generally the time player count is is last 30-60 minutes, so get the real time for popover
$realTimeUsed = floor((time() - strtotime('-30 minutes', normalizeTime())) / 60);

echo <<<END

<script type="text/javascript">
    $(document).ready(function() {
        $("#players-popover").popover();
    });
</script>

<div class="hero-unit">
    <h1 style="margin-bottom:10px; font-size:57px;">Glorious plugin stats.</h1>
    <p>MCStats / Plugin Metrics is the de facto statistical engine for Minecraft, actively used by over <b>$pluginCount</b> plugins.</p>
    <p>Across the world, over <b>$playerCount</b> players have been seen <b>in the last 30 minutes</b> on over <b>$serverCount</b> servers.</p>
    <p><a href="/learn-more/" class="btn btn-success"><i class="icon-white icon-star-empty"></i> Learn More</a> :: <a class="btn btn-primary" href="/plugin-list/"><i class="icon-white icon-th-list"></i> Plugin List</a></p>
</div>

<div class="row" style="text-align: center;">
    <h1 style="margin-bottom:30px; font-size:40px;">4 of the top 100 plugins. Do you use them?</h1>
</div>

<div class="row" style="text-align: center;">
END;

$first = true;
foreach (loadPlugins(PLUGIN_ORDER_RANDOM_TOP100, 4) as $plugin)
{
    $name = htmlentities($plugin->getName());
    $authors = htmlentities($plugin->getAuthors());

    // check for spaces or commas (and if they exist, throw is (s) after Author
    $author_prepend = '';
    if (strstr($authors, ' ') !== FALSE || strstr($authors, ',') !== FALSE)
    {
        $author_prepend = '(s)';
    }

    echo '
    <div class="span3">
        <h2 style="margin-bottom:7px;"><b>' . $name . '</b></h2>
        <p>
            ' . (empty ($authors) ? '' : ('Author' . $author_prepend . ': ' . $authors)) . ' <br/>
            Started ' . number_format($plugin->getGlobalHits()) . ' times <br/>
            Servers (last 24 hrs): ' . number_format($plugin->getServerCount()) . '
        </p>
        <p>
            <img src="/plugin-preview/' . $name . '" />
        </p>
        <p><a class="btn" href="/plugin/' . htmlentities($plugin->getName()) . '" target="_blank">More info &raquo;</a></p>
    </div>
';
    $first = false;
}

echo '</div>';

send_footer();