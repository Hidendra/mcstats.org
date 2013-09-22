<?php
define('ROOT', '../');
session_start();

require_once ROOT . '../private_html/config.php';
require_once ROOT . '../private_html/includes/database.php';
require_once ROOT . '../private_html/includes/func.php';

if (!isset($_GET['guid'])) {
    header('Location: /');
    exit;
}

/// Templating
$page_title = 'MCStats :: Server Info';
$container_class = 'container';
send_header();

$server = getServerRowForGUID($_GET['guid']);

if (count($server) == 0) {
    echo '<br/> <div class="alert alert-error">No such server exists.</div>';
    send_footer();
    exit;
}

$query = get_slave_db_handle()->prepare('
SELECT Plugin.Name, ServerPlugin.Version as Version, ServerPlugin.Revision as Revision, (UNIX_TIMESTAMP()-Updated) as LastSent
FROM Server
LEFT OUTER JOIN ServerPlugin on Server.ID = ServerPlugin.Server
LEFT OUTER JOIN Plugin on Plugin.ID = ServerPlugin.Plugin
WHERE GUID = ? order by LastSent asc;
');
$query->execute(array($server['GUID']));

$pluginCount = 0;
$pluginsHTML = '';

while ($row = $query->fetch()) {
    $safeName = htmlentities($row['Name']);
    $safeUrlName = urlencode($safeName);
    $pluginCount++;

    $pluginsHTML .= '<tr>
                        <td> <a href="/plugin/' . $safeUrlName . '">' . $safeName . '</a> </td>
                        <td> ' . $row['Version'] . ' </td>
                        <td> ' . $row['Revision'] . ' </td>
                        <td> ' . epochToHumanString($row['LastSent']) . ' ago </td>
                    </tr>
';
}

$query = get_slave_db_handle()->prepare('
SELECT Plugin.Name, CustomColumn.ID, Graph.Name as GraphName, CustomColumn.Name as ColumnName, CustomData.DataPoint, (UNIX_TIMESTAMP()-ServerPlugin.Updated) as LastSent FROM CustomData
LEFT OUTER JOIN Plugin on Plugin.ID = CustomData.Plugin
LEFT OUTER JOIN CustomColumn ON CustomColumn.ID = CustomData.ColumnID
LEFT OUTER JOIN Graph ON Graph.ID = CustomColumn.Graph
LEFT OUTER JOIN Server on Server.ID = CustomData.Server
LEFT OUTER JOIN ServerPlugin ON Server.ID = ServerPlugin.Server WHERE Server.GUID = ? group by CustomColumn.ID order by Plugin.Name asc, LastSent asc limit 1000
');
$query->execute(array($server['GUID']));

$customDataHTML = '';

$c = 0;
while ($row = $query->fetch()) {
    $safeName = htmlentities($row['Name']);
    $safeUrlName = urlencode($safeName);

    $customDataHTML .= '<tr>
                        <td> <a href="/plugin/' . $safeUrlName . '">' . $safeName . '</a> </td>
                        <td> ' . htmlentities($row['GraphName']) . '</td>
                        <td> ' . htmlentities($row['ColumnName']) . '</td>
                        <td> ' . $row['DataPoint'] . '</td>
                        <td> ' . epochToHumanString($row['LastSent']) . ' ago </td>
                    </tr>
';

    $c++;
    if ($c >= 1000) {
        $customDataHTML .= '<tr> <td> DATA TRIMMED, > 1000 ROWS </td> <td> </td> <td>  </td> <td>  </td> </tr>';
    }
}

$onlineMode = $server['online_mode'] == 1 ? '<td style="background-color: #90EE90;"> Yes </td>' : '<td style="background-color: #FA8072;"> No </td>';

echo <<<END


<table class="table">

    <tbody>
    </tbody>

</table>

<div class="span8 offset4">
    <div class="widget-box">
        <div class="widget-title">
            <span class="icon">
                <i class="icon-th"></i>
            </span>
            <h5>Server Details</h5>
        </div>
        <div class="widget-content nopadding">
            <table class="table table-bordered table-striped table-hover">
                <tbody>
                    <tr>
                        <td> GUID </td>
                        <td> {$server['GUID']} </td>
                    </tr>
                    <tr>
                        <td> Online Mode ? </td>
                        {$onlineMode}
                    </tr>
                    <tr>
                        <td> Players Online </td>
                        <td> {$server['Players']} </td>
                    </tr>
                    <tr>
                        <td> Country </td>
                        <td> {$server['Country']} </td>
                    </tr>
                    <tr>
                        <td> CPU Cores </td>
                        <td> {$server['cores']} </td>
                    </tr>
                    <tr>
                        <td> Server Software </td>
                        <td> {$server['ServerSoftware']} </td>
                    </tr>
                    <tr>
                        <td> Minecraft Version </td>
                        <td> {$server['MinecraftVersion']} </td>
                    </tr>
                    <tr>
                        <td> Software (raw) </td>
                        <td> {$server['ServerVersion']} </td>
                    </tr>
                    <tr>
                        <td> Operating System </td>
                        <td> {$server['osname']} {$server['osversion']} (arch: {$server['osarch']}) </td>
                    </tr>
                    <tr>
                        <td> Java Version </td>
                        <td> {$server['java_name']}.{$server['java_version']} </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="span8 offset4">
    <div class="widget-box">
        <div class="widget-title">
            <span class="icon">
                <i class="icon-th"></i>
            </span>
            <h5>Plugins (total: {$pluginCount})</h5>
        </div>
        <div class="widget-content nopadding">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th> Plugin </th>
                        <th> Version </th>
                        <th> Revision </th>
                        <th> Last Sent Data </th>
                    </tr>
                </thead>

                <tbody>
{$pluginsHTML}
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="span8 offset4">
    <div class="widget-box">
        <div class="widget-title">
            <span class="icon">
                <i class="icon-th"></i>
            </span>
            <h5>Custom Data</h5>
        </div>
        <div class="widget-content nopadding">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th> Plugin </th>
                        <th> Graph Name </th>
                        <th> Column Name </th>
                        <th> Data </th>
                        <th> Sent </th>
                    </tr>
                </thead>

                <tbody>
{$customDataHTML}
                </tbody>
            </table>
        </div>
    </div>
</div>

END;


send_footer();