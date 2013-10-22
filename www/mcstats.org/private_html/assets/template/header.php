<?php

// Find out our current working directory
$cwd = getcwd();

$is_in_admin_ui = str_endswith('admin', $cwd);
$currentFile = $_SERVER["PHP_SELF"];
$parts = explode('/', $currentFile);
$fileNameWithExt = $parts[count($parts) - 1];
$fileName = strtolower(substr($fileNameWithExt, 0, strpos($fileNameWithExt, '.')));

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8"/>
    <title><?php global $page_title;
        echo(isset($page_title) ? $page_title : 'Metrics - Admin'); ?></title>scroll
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="description" content=""/>
    <meta name="author" content="Tyler Blair <hidendra@griefcraft.com>"/>

    <meta name="viewport" content="width=device-width">

    <!-- css -->
    <link href="http://static.mcstats.org/css/libraries/bootstrap/bootstrap.min.css" rel="stylesheet"/>
    <link href="http://static.mcstats.org/css/libraries/font-awesome/font-awesome.min.css" rel="stylesheet"/>
    <link href="http://static.mcstats.org/css/libraries/template/template.min.css" rel="stylesheet"/>
    <link href="http://static.mcstats.org/css/libraries/template/template.blue.min.css" rel="stylesheet"/>
    <link href="http://static.mcstats.org/css/libraries/jquery/typeahead.js-bootstrap.css" rel="stylesheet"/>
    <link href="http://static.mcstats.org/css/libraries/famfamfam/fam-icons.css" rel="stylesheet"/>

    <!-- core libs -->
    <script src="http://static.mcstats.org/javascript/libraries/jquery/jquery.min.js" type="text/javascript"></script>
    <script src="http://static.mcstats.org/javascript/libraries/jquery/jquery.pjax.js" type="text/javascript"></script>
    <script src="http://static.mcstats.org/javascript/libraries/jquery/jquery.jpanelmenu.min.js" type="text/javascript"></script>
    <script src="http://static.mcstats.org/javascript/libraries/jquery/jquery.sparkline.min.js" type="text/javascript"></script>
    <script src="http://static.mcstats.org/javascript/libraries/bootstrap/bootstrap.min.js" type="text/javascript"></script>
    <script src="http://static.mcstats.org/javascript/libraries/bootstrap/tooltip.js" type="text/javascript"></script>
    <script src="http://static.mcstats.org/javascript/libraries/bootstrap/typeahead.min.js" type="text/javascript"></script>
    <script src="http://static.mcstats.org/javascript/libraries/template/template.min.js" type="text/javascript"></script>

    <!-- charting -->
    <script src="http://static.mcstats.org/javascript/libraries/highcharts/highstock.js" type="text/javascript"></script>
    <script src="http://static.mcstats.org/javascript/libraries/highcharts/themes/simplex.js" type="text/javascript"></script>
    <script src="http://static.mcstats.org/javascript/libraries/highcharts/exporting.js" type="text/javascript"></script>

    <!-- mcstats -->
    <script src="http://static.mcstats.org/javascript/mcstats.js" type="text/javascript"></script>

    <script type='text/javascript' src='https://www.google.com/jsapi'></script>

    <script type="text/javascript">
        // Google analytics
        var _gaq = _gaq || [];
        _gaq.push(['_setAccount', 'UA-31036792-1']);
        _gaq.push(['_setDomainName', 'mcstats.org']);
        _gaq.push(['_setAllowLinker', true]);
        _gaq.push(['_trackPageview']);

        (function () {
            var ga = document.createElement('script');
            ga.type = 'text/javascript';
            ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0];
            s.parentNode.insertBefore(ga, s);
        })();

        google.load('visualization', '1', {'packages': ['geochart']});
    </script>
</head> <?php flush(); ?>

<body>
<div id="header">
    <h1><a href="/">MCStats / Plugin Metrics</a></h1>
</div>

<div id="search">
    <form action="" method="post" onsubmit="window.location='/plugin/' + $('#goto').val(); return false;">
        <input type="text" id="goto" placeholder="Go to Plugin" autocomplete="off"/><button type="submit" class="tip-right" title="Go"><i class="icon-search"></i></button>
    </form>
</div>

<div id="user-nav">
    <?php

    if (is_loggedin()) {
        $accessible_plugins = get_accessible_plugins(false);
        $plugin_count = count($accessible_plugins);

        $plugins_html = '';
        foreach ($accessible_plugins as $plugin) {
            $safeName = htmlentities($plugin->getName());
            $plugins_html .= '<li><a href="/admin/plugin/' . $safeName . '/view">' . $safeName . '</a></li>';
        }
        if ($plugins_html == '') {
            $plugins_html = '<li><a href="#">No plugins</a></li>';
        }

        echo <<<END
            <ul class="btn-group">
                <li class="btn dropdown" id="menu-messages"><a href="#" data-toggle="dropdown" data-target="#menu-messages" class="dropdown-toggle"><i class="icon icon-envelope"></i> <span class="text">Plugins</span> <span class="label label-important">$plugin_count</span> <b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        $plugins_html
                    </ul>
                </li>
                <li class="btn"><a title="" href="/admin/logout.php"><span class="text">Logout</span> <i class="icon icon-share-alt"></i></a></li>
            </ul>
END;

    } else {

        echo <<<END
            <ul class="btn-group">
                <li class="btn"><a title="" href="/admin/login.php"><i class="icon icon-share-alt"></i> <span class="text">Login</span></a></li>
            </ul>
END;


    }

    ?>
</div>

<div id="sidebar">
    <ul>
        <li<?php if ($fileName == 'index') {
            echo ' class="active"';
        } ?>><a href="/"><i class="icon icon-home"></i> <span>Homepage</span></a>
        </li>
        <li<?php if ($fileName == 'plugin-list') {
            echo ' class="active"';
        } ?>><a href="/plugin-list/"><i class="icon icon-list-alt"></i> <span>Plugin List</span></a></li>
        <li<?php if ($fileName == 'global-stats') {
            echo ' class="active"';
        } ?>><a href="/global/"><i class="icon icon-signal"></i> <span>Global Statistics</span></a></li>
        <li><a href="/status/"><i class="icon icon-retweet"></i> <span>Backend Status</span></a></li>
        <?php global $sidebar_more;
        if (isset($sidebar_more)) {
            echo $sidebar_more;
        } ?>
        <li class="submenu<?php if ($is_in_admin_ui) {
            echo ' active open';
        } ?>">
            <a href="#"><i class="icon icon-wrench"></i> <span>Plugin Admin</span> <span class="label">2</span></a>
            <ul>
                <li<?php if ($is_in_admin_ui && $fileName == 'index') {
                    echo ' class="active"';
                } ?>><a href="/admin/">Admin home</a></li>
                <li<?php if ($fileName == 'addplugin') {
                    echo ' class="active"';
                } ?>><a href="/admin/add-plugin/">Add a plugin</a></li>
            </ul>
        </li>

        <li>
            <a><span>A very special thanks to the MCStats sponsors:</span></a>

            <ol style="padding: 0; margin: 0;" class="sponsors">
                <li><a href="http://buycraft.net" target="_blank"><img src="http://static.mcstats.org/img/sponsors/buycraft.png" width="210px" style="padding-left: 10px" /></a></li>
                <li><a href="http://avnk.net" target="_blank"><img src="http://static.mcstats.org/img/sponsors/Avalanche-Network-v5.png" width="210px" /></a></li>
                <li><a href="https://twitter.com/VladToBeHere" target="_blank"><span style="margin-left: 15px; font-size: 24px; color: #428BCA">@VladToBeHere</span></a></li>
            </ol>
        </li>
    </ul>

</div>

<div id="content">
    <div id="content-header">
        <h1>MCStats / Plugin Metrics</h1>
    </div>

    <div id="breadcrumb">
        <a href="/" title="Home"
           class="tip-bottom<?php if (!$is_in_admin_ui && $fileName == 'index') {
               echo ' current';
           } ?>"><i
                class="icon-home"></i> Home</a>
        <?php global $breadcrumbs;
        if (isset($breadcrumbs)) {
            echo $breadcrumbs;
        } ?>
    </div>

    <div class="container-fluid">
        <div class="row-fluid" id="graph-generator" style="display: none">
            <div>
                <div class="alert alert-info col-xs-6"
                     style="width: 50%; padding-bottom: 0; margin-left: 25%; text-align: center; float: left;">
                    <p>
                        <strong>INFO:</strong> Graphs are currently generating.
                    </p>
                </div>

                <div class="progress progress-striped progress-success active" style="clear: left">
                    <div class="bar" id="graph-generator-progress-bar" style="width: 0"></div>
                </div>
            </div>
        </div>
