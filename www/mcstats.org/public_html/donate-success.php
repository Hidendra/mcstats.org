<?php
define('ROOT', './');
session_start();

require_once ROOT . '../private_html/config.php';
require_once ROOT . '../private_html/includes/database.php';
require_once ROOT . '../private_html/includes/func.php';

/// Templating
$page_title = 'MCStats :: Thank you';
$breadcrumbs = '<a href="/donate/" class="current">Thank you</a>';
// $container_class = 'container';
send_header();

echo <<<END
<div class="row-fluid">
    <div class="widget-box span8 offset2">
        <div class="widget-content">
            <p style="font-size: 16px;">
                Thank you very much for your donation to MCStats / Plugin Metrics.
            </p>
        </div>
    </div>
</div>
END;

send_footer();

?>