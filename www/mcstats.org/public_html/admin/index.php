<?php

define('ROOT', '../');
session_start();

require_once ROOT . '../private_html/config.php';
require_once ROOT . '../private_html/includes/database.php';
require_once ROOT . '../private_html/includes/func.php';

ensure_loggedin();
cacheCurrentPage();

$breadcrumbs = '<a href="/admin/" class="current">Administration</a>';
send_header();
?>

    <div class="col-xs-12">
        <h1 style="margin-bottom:10px; font-size:57px;">Welcome!</h1>

        <p>
            Under the <i>Plugins</i> menu at the top you will find plugins you have access to. From there you can manage
            them and edit settings for them.
        </p>

        <p>
            <a href="/admin/add-plugin/" class="btn btn-success btn-large"><i class="icon-white icon-heart"></i> Add a
                Plugin</a>
        </p>
    </div>

<?php

send_footer();