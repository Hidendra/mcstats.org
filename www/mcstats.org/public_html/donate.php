<?php
define('ROOT', './');
session_start();

require_once ROOT . 'config.php';
require_once ROOT . 'includes/database.php';
require_once ROOT . 'includes/func.php';

/// Templating
$page_title = 'MCStats :: Donate to MCStats';
// $container_class = 'container';
send_header();

echo '

<div class="row-fluid" style="margin-left: 25%; text-align: center;">
    <div class="span6" style="width: 50%;">
        <h1 style="margin-bottom:30px; font-size:40px;">
            Serving you rock solid stats.
        </h1>
    </div>
</div>

<div class="row-fluid" style="margin-left: 25%;">
    <div class="span6 well" style="width: 50%;">
        <p style="font-size: 16px;">
            The MCStats / Plugin Metrics backend receives <b>over 400 requests per second</b> 24 hours a day, 7 days a week
        </p>
        <p style="font-size: 16px;">
            That is over <b><span style="font-size: 20px;">1 billion</span> requests per month</b> and over <b><span style="font-size: 20px;">30 million</span> requests each day</b> and it will only <b>continue to rise</b>
        </p>
        <p>
            Every single server is tracked. To get useful data for plugins, each server needs to be identified and from there
            data is stored such as the plugins a server is using (that support MCStats), the amount of players online,
            and even the Minecraft version the server is on including the server software (e.g CraftBukkit).
        </p>
        <p>
            And this is no simple task. It requires a lot of power and it also needs room for growth. Right now the service
            is <b>entirely funded by myself, Hidendra</b> and no outside sources fund the service in any way.
        </p>';

// appeal to their plugins they have if they are logged in
if (is_loggedin() && ($pluginCount = count($plugins = get_accessible_plugins())) > 0)
{
    // shuffle the plugins
    shuffle($plugins);

    // use the first one
    $plugin = $plugins[0];

    echo '
        <p>
            You have ' . $pluginCount . ' plugin' . ($pluginCount > 1 ? 's' : '') . ' that collects data from servers attached to MCStats / Plugin Metrics.
            Even if you only have one plugin, or a dozen, you have still helped MCStats in a tremendous way by helping
            it rise to where it is today. And I\'m sure this has also been mutual -- this service has helped you
            see live, real world statistics about your plugin that download counters can\'t give you.
        </p>
    </div>
</div>
<div class="row-fluid">
    <div style="text-align: center;">
        <p>
            <img src="/signature/' . urlencode(htmlentities($plugin->getName())) . '.png" />
        </p>
';
} else
{
    echo '
        <p>
            MCStats / Plugin Metrics is unrivaled in stat collection for Minecraft plugins. Many of the plugin authors
            who decided to use MCStats had an eye-opening experience. Seeing real numbers in a beautiful format is
            amazing and for some, gives them the motivation to continue with plugin development.
        </p>
    </div>
</div>
<div class="row-fluid">
    <div style="text-align: center;">
        <p>
            <img src="/signature/all+servers.png" />
        </p>
';
}

echo '
        <p>
            So donate today. Give as little as a dollar, or as much as you want, or don\'t donate at all -- the decision is yours and yours alone.
        </p>
    </div>
</div>

<div class="row-fluid">
    <div style="text-align: center;">
        <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
            <input type="hidden" name="cmd" value="_s-xclick">
            <input type="hidden" name="hosted_button_id" value="UWDNQHSKFZX4U">
            <input type="hidden" name="amount" value="100">
            <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" style="height: 47px; width: 147px;" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
            <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
        </form>
    </div>
</div>

';

send_footer();

?>