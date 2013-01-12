<?php
define('ROOT', './');
session_start();

require_once ROOT . '../private_html/config.php';
require_once ROOT . '../private_html/includes/database.php';
require_once ROOT . '../private_html/includes/func.php';

header('Last-Modified: ' . gmdate('D, d M Y H:i:s', getLastGraphEpoch()) . ' GMT');

/// Templating
$page_title = 'MCStats :: Privacy Policy';
// $container_class = 'container';
send_header();

echo <<<END

<div class="row-fluid" style="margin-left: 25%; text-align: center;">
    <div class="span6" style="width: 50%;">
        <h1 style="margin-bottom:30px; font-size:40px;">
            Privacy Policy
        </h1>
    </div>
</div>

<div class="row-fluid" style="margin-left: 25%; text-align: center;">
    <div class="span6" style="width: 50%;">
        <p>
            All plugins that use MCStats will send data to a central server (http://mcstats.org)
        </p>
    </div>
</div>

<div class="row-fluid" style="margin-left: 30%;">
    <div class="span6" style="width: 40%;">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th> Variable </th>
                    <th> I/O type </th>
                    <th> Sent to MCStats </th>
                    <th> Notes </th>
                </tr>
            </thead>

            <tbody>

                <tr>
                    <td> File contents of <code>plugins/PluginMetrics/config.yml</code> </td>
                    <td> Read/write </td>
                    <td> No </td>
                    <td> File is created if it does not already exist. </td>
                </tr>

                <tr>
                    <td> Server's GUID </td>
                    <td> Read/write </td>
                    <td> <b>Yes</b> </td>
                    <td> Only written if it needs to be generated. </td>
                </tr>

                <tr>
                    <td> Players currently online </td>
                    <td> Read </td>
                    <td> <b>Yes</b> </td>
                    <td>  </td>
                </tr>

                <tr>
                    <td> Server version </td>
                    <td> Read </td>
                    <td> <b>Yes</b> </td>
                    <td>  </td>
                </tr>

                <tr>
                    <td> Plugin version that is submitted </td>
                    <td> Read </td>
                    <td> <b>Yes</b> </td>
                    <td>  </td>
                </tr>

                <tr>
                    <td> MineShafter active (yes/no) </td>
                    <td> Read </td>
                    <td> <b>Yes</b> </td>
                    <td>  </td>
                </tr>

                <tr>
                    <td> Plugin's custom data </td>
                    <td> Read/<i>write</i> </td>
                    <td> <b>Yes</b> </td>
                    <td> Custom data is controlled by the author. I am not responsible for what is sent here. Data may or may not be written to the disk depending on the plugin. </td>
                </tr>

                <tr>
                    <td> Operation System </td>
                    <td> Read </td>
                    <td> <b>Yes</b> </td>
                    <td> Added in R6. Copies functionality of Minecraft's own Snooper. </td>
                </tr>

                <tr>
                    <td> OS Architecture </td>
                    <td> Read </td>
                    <td> <b>Yes</b> </td>
                    <td> Added in R6. Copies functionality of Minecraft's own Snooper. </td>
                </tr>

                <tr>
                    <td> # CPU Cores </td>
                    <td> Read </td>
                    <td> <b>Yes</b> </td>
                    <td> Added in R6. Copies functionality of Minecraft's own Snooper. </td>
                </tr>

                <tr>
                    <td> Auth mode (online-mode yes/no) </td>
                    <td> Read </td>
                    <td> <b>Yes</b> </td>
                    <td> Added in R6. Copies functionality of Minecraft's own Snooper. </td>
                </tr>

            </tbody>
        </table>
    </div>
</div>

END;

send_footer();