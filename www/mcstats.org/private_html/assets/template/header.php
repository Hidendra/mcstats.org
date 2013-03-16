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
        <meta charset="utf-8" />
        <title><?php global $page_title; echo (isset($page_title) ? $page_title : 'Metrics - Admin'); ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="description" content="" />
        <meta name="author" content="Tyler Blair <hidendra@griefcraft.com>" />

        <meta name="viewport" content="width=device-width">

        <!-- contains all .css files minified -->
        <link href="https://d2jz01fyat1phn.cloudfront.net/css/combined.css" rel="stylesheet" />

        <!-- jquery, main, bootstrap -->
        <script src="https://d2jz01fyat1phn.cloudfront.net/javascript/bootstrap-combined-jquery.js" type="text/javascript"></script>

        <script type="text/javascript">
            // Google analytics
            var _gaq = _gaq || [];
            _gaq.push(['_setAccount', 'UA-31036792-1']);
            _gaq.push(['_setDomainName', 'mcstats.org']);
            _gaq.push(['_setAllowLinker', true]);
            _gaq.push(['_trackPageview']);

            (function() {
                var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
                ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
            })();
        </script>
    </head> <?php flush(); ?>

    <body>
        <div id="header">
            <h1><a href="/">MCStats / Plugin Metrics</a></h1>
        </div>

        <div id="search">
            <form action="" method="post" onsubmit="window.location='/plugin/' + $('#goto').val(); return false;">
                <input type="text" id="goto" placeholder="Plugin search" autocomplete="off"/><button type="submit" class="tip-right" title="Go to plugin"><i class="icon-share-alt icon-white"></i></button>
            </form>
        </div>

        <div id="user-nav" class="navbar navbar-inverse">
            <?php

            if (is_loggedin())
            {
                $accessible_plugins = get_accessible_plugins(false);
                $plugin_count = count($accessible_plugins);

                $plugins_html = '';
                foreach ($accessible_plugins as $plugin)
                {
                    $safeName = htmlentities($plugin->getName());
                    $plugins_html .= '<li><a href="/admin/plugin/' . $safeName . '/view">' . $safeName . '</a></li>';
                }
                if ($plugins_html == '') {
                    $plugins_html = '<li><a href="#">No plugins</a></li>';
                }

                echo <<<END
            <ul class="nav btn-group">
                <li class="btn btn-inverse dropdown" id="menu-messages"><a href="#" data-toggle="dropdown" data-target="#menu-messages" class="dropdown-toggle"><i class="icon icon-envelope"></i> <span class="text">Plugins</span> <span class="label label-important">$plugin_count</span> <b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        $plugins_html
                    </ul>
                </li>
                <li class="btn btn-inverse"><a title="" href="/admin/logout.php"><span class="text">Logout</span> <i class="icon icon-share-alt"></i></a></li>
            </ul>
END;

            } else
            {

                echo <<<END
            <ul class="nav btn-group">
                <li class="btn btn-inverse"><a title="" href="/admin/login.php"><i class="icon icon-share-alt"></i> <span class="text">Login</span></a></li>
            </ul>
END;


            }

            ?>
        </div>

        <div id="sidebar">
            <ul>
                <li<?php if ($fileName == 'index') echo ' class="active"'; ?>><a href="/"><i class="icon icon-home"></i> <span>Homepage</span></a></li>
                <li<?php if ($fileName == 'plugin-list') echo ' class="active"'; ?>><a href="/plugin-list/"><i class="icon icon-list-alt"></i> <span>Plugin List</span></a></li>
                <li<?php if ($fileName == 'global-stats') echo ' class="active"'; ?>><a href="/global-stats.php"><i class="icon icon-signal"></i> <span>Global Statistics</span></a></li>
                <li><a href="/status/"><i class="icon icon-retweet"></i> <span>Backend Status</span></a></li>
<?php global $sidebar_more; if (isset($sidebar_more)) echo $sidebar_more; ?>
                <li class="submenu<?php if ($is_in_admin_ui) echo ' active open'; ?>">
                    <a href="#"><i class="icon icon-wrench"></i> <span>Administration</span> <span class="label">2</span></a>
                    <ul>
                        <li<?php if ($is_in_admin_ui && $fileName == 'index') echo ' class="active"'; ?>><a href="/admin/">Admin home</a></li>
                        <li<?php if ($fileName == 'addplugin') echo ' class="active"'; ?>><a href="/admin/add-plugin/">Add a plugin</a></li>
                    </ul>
                </li>
                <li class="submenu<?php if ($fileName == 'reports') echo ' active open'; ?>">
                    <a href="#"><i class="icon icon-book"></i> <span>Reports</span> <span class="label">2</span></a>
                    <ul>
                        <li<?php if (isset($_GET['period']) && $_GET['period'] == 'december-2012') echo ' class="active"'; ?>><a href="/reports/january-2013/">January 2013</a></li>
                        <li<?php if (isset($_GET['period']) && $_GET['period'] == 'december-2012') echo ' class="active"'; ?>><a href="/reports/december-2012/">December 2012</a></li>
                    </ul>
                </li>
            </ul>

        </div>

        <div id="content">
            <div id="content-header">
                <h1>MCStats / Plugin Metrics</h1>
            </div>

            <div id="breadcrumb">
                <a href="/" title="Home" class="tip-bottom<?php if (!$is_in_admin_ui && $fileName == 'index') echo ' current'; ?>"><i class="icon-home"></i> Home</a>
                <?php global $breadcrumbs; if (isset($breadcrumbs)) echo $breadcrumbs; ?>
            </div>

            <div class="container-fluid">

