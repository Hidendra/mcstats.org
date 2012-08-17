<?php
define('ROOT', './');
session_start();

require_once ROOT . 'config.php';
require_once ROOT . 'includes/database.php';
require_once ROOT . 'includes/func.php';

/// Templating
$page_title = 'MCStats :: Thank you';
// $container_class = 'container';
send_header();

echo '

<div class="row-fluid" style="margin-left: 25%;">
    <div class="span6 well well-large" style="width: 50%;">
        <p style="font-size: 16px;">
            Thank you very much for your donation to MCStats / Plugin Metrics.
        </p>
        <p style="font-size: 16px;">
            Your donation means my pockets will contain more than just lint and for that I am very grateful.
        </p>
    </div>
</div>';

send_footer();

?>