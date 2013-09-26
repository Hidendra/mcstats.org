<?php
define('ROOT', './');
session_start();

require_once ROOT . '../private_html/config.php';
require_once ROOT . '../private_html/includes/database.php';
require_once ROOT . '../private_html/includes/func.php';
cacheCurrentPage();

/// Templating
$page_title = 'MCStats :: Why MCStats?';
$breadcrumbs = '<a href="/learn-more/" class="current">Learn More</a>';

// $container_class = 'container';
send_header();

echo <<<END

<div class="row-fluid" style="margin-left: 20%; text-align: center;">
    <div class="col-xs-6" style="width: 60%;">
        <h1 style="margin-bottom:30px; font-size:40px;">
            MCStats is a unique service that is entirely open.
        </h1>
    </div>
</div>

<div class="row-fluid">
    <div class="col-xs-10 col-md-offset-1">
        <div class="widget-box">
            <div class="widget-content">
                <div class="row-fluid">
                    <p style="font-size: 16px;">
                        MCStats is <b>free</b>, <b>open source</b> and <b>anonymous</b>. All data is public and freely available for every plugin.
                    </p>
                    <p>
                        The project started as an idea to create an open source stats system for <i>LWC</i>. I wanted to share this with
                        any other author too and so I slowly built the system up. It has became very powerful today and for that I am
                        very proud of what has been done already.
                    </p>
                    <p>
                        Some plugins out there use a significantly less powerful system for tracking plugin usage but <b><i>they do not
                        tell you about it</i></b> nor can you see the code that is being used, so you can never be sure they're not doing
                        something bad.
                    </p>
                    <p>
                        While MCStats forces plugins to show their data to everyone, it also means they are proud to show <i>you</i>
                        the data they're collecting with it. I believe this is a step in the right direction and something all authors
                        should strive for with transparency.
                    </p>
                    <p style="text-align: center;">
                        <img src="http://api.mcstats.org/plugin-preview/2.0/all+servers.png" />
                    </p>
                    <p style="font-size: 16px;">
                        <b>IRC:</b> <code>irc.esper.net #metrics</code>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row-fluid">
    <div class="col-xs-5 col-md-offset-1">
        <div class="widget-box">
            <div class="widget-title"><span><i></i></span><h4 style="float: left; margin-left: 40%;">Plugin Authors</h4></div>
            <div class="widget-content">
                <div class="row-fluid">
                    <p>
                        It is very easy to add MCStats / Plugin Metrics to your plugin. You can be up and running in less than 5 minutes and
                        you will have immediate access to everything that is available.
                    </p>
                    <p style="text-align: center;">
                        <a href="/admin/" class="btn btn-success" target="_blank"><i class="icon-white icon-heart"></i> Register / Login</a>
                    </p>
                    <p>
                        If you run into any troubles or have a question please do visit us in IRC or email me directly: <code>hidendra [at] mcstats.org</code>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xs-5">
        <div class="widget-box">
            <div class="widget-title"><span><i></i></span><h4 style="float: left; margin-left: 40%;">Server Owners</h4></div>
            <div class="widget-content">
                <div class="row-fluid">
                    <p>
                        For this service to track a plugin, an author must explicitly add it to their plugin. They will most likely have made
                        note of this addition in any changelogs. Your server <i>cannot</i> be identified nor controlled in any way by MCStats.
                        As well, <b>you have access to the same data that the plugin author can see</b>.
                    </p>

                    <p>
                        You are free to opt-out of submitting data whenever you wish. This will immediately stop sending data for any plugins that supports MCStats / Plugin Metrics.
                        Simply edit <code>plugins/PluginMetrics/config.yml</code> and change <code>opt-out: false</code> to <code>true</code>
                    </p>

                    <p>
                        If you have any questions at all about how this service operates or anything else, please do visit us in IRC or email me directly: <code>hidendra [at] mcstats.org</code>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="clearfix"></div>

END;

send_footer();

?>