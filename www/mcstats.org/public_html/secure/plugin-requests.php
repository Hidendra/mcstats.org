<?php
define('ROOT', '../');
session_start();

require_once ROOT . '../private_html/config.php';
require_once ROOT . '../private_html/includes/database.php';
require_once ROOT . '../private_html/includes/func.php';

if (isset($_POST['submit'])) {
    $authorID = $_POST['author'];
    $pluginID = $_POST['plugin'];
    $email = trim($_POST['email']);
    $action = $_POST['submit'];
    $approved = $action == 'Accept';

    // load the plugin
    $plugin = loadPluginByID($pluginID);

    if ($approved) {
        $statement = $master_db_handle->prepare('UPDATE AuthorACL SET Pending = 0 WHERE Author = ? AND Plugin = ?');
        $statement->execute(array($authorID, $pluginID));
    } else {
        $statement = $master_db_handle->prepare('DELETE FROM AuthorACL WHERE Author = ? and Plugin = ?');
        $statement->execute(array($authorID, $pluginID));
    }

    // both actions require the request to become fulfilled
    $statement = $master_db_handle->prepare('UPDATE PluginRequest SET Complete = 1 WHERE Author = ? and Plugin = ? AND Complete = 0');
    $statement->execute(array($authorID, $pluginID));

    // Should we send an email ?
    if (!empty($email)) {
        sendPluginRequestEmail($email, $plugin, $approved);
    }

    header('Location: /secure/plugin-requests.php');
    exit;
}

/// Templating
$page_title = 'MCStats :: Secure';
send_header();

echo '
<div class="row" style="margin-left: 5%;">
    <table class="table table-striped" style="width: 90%;">
        <thead>
            <tr>
                <th> Author (ID) (# ACLs) </th>
                <th> Plugin (ID) (# ACLs) </th>
                <th> DBO </th>
                <th> Email </th>
                <th> Request creation </th>
                <th> Plugin creation </th>
                <th> </th>
            </tr>
        </thead>

        <tbody>';


$statement = get_slave_db_handle()->prepare('SELECT
                                    -- Author rows
                                    Author.ID AS AuthorID, Author.Name AS AuthorName,

                                    -- Plugin, match resolvePlugin
                                    Plugin.ID AS ID, Parent, Plugin.Name AS Name, Plugin.Author AS Author, Hidden, GlobalHits, Plugin.Created AS Created, Plugin.LastUpdated as LastUpdated, Plugin.Rank as Rank, Plugin.LastRank as LastRank, Plugin.LastRankChange as LastRankChange, Plugin.ServerCount30 as ServerCount30,

                                    -- Generic
                                    Email, DBO, PluginRequest.Created AS RequestCreated FROM PluginRequest
                                    LEFT OUTER JOIN Author on Author.ID = PluginRequest.Author
                                    LEFT OUTER JOIN Plugin ON Plugin.ID = PluginRequest.Plugin
                                    WHERE PluginRequest.Complete = 0
                                    ORDER BY PluginRequest.Created ASC');
$statement->execute();

while ($row = $statement->fetch()) {
    $authorID = $row['AuthorID'];
    $authorName = $row['AuthorName'];
    $email = $row['Email'];
    $dbo = $row['DBO'];
    $created = $row['RequestCreated'];

    // resolve the plugin
    $plugin = resolvePlugin($row);

    if (strstr($dbo, 'http') !== false || strstr($dbo, 'com') !== false || strstr($dbo, 'org')) {
        $dbo_link = '<a href="' . htmlentities($dbo) . '" target="_blank">' . htmlentities($dbo) . '</a>';
    } else {
        $dbo_link = htmlentities($dbo);
    }

    $authorsStatement = get_slave_db_handle()->prepare('SELECT COUNT(*) FROM AuthorACL WHERE Plugin = ? AND Pending = 0');
    $authorsStatement->execute(array($plugin->getID()));
    $existingAuthors = $authorsStatement->fetch()[0];

    $authorsStatement = get_slave_db_handle()->prepare('SELECT COUNT(*) FROM AuthorACL WHERE Author = ? AND Pending = 0');
    $authorsStatement->execute(array($authorID));
    $existingOwnedPlugins = $authorsStatement->fetch()[0];

    echo '
            <tr>
                <td>
                    ' . htmlentities($authorName) . ' (' . $authorID . ') (' . $existingOwnedPlugins . ')
                </td>
                <td>
                    ' . htmlentities($plugin->getName()) . ' (' . $plugin->getID() . ') (' . $existingAuthors . ')
                </td>
                <td>
                    ' . $dbo_link . '
                </td>
                <td>
                    ' . htmlentities($email) . '
                </td>
                <td>
                    ' . epochToHumanString(time() - $created, false) . ' ago
                </td>
                <td>
                    ' . epochToHumanString(time() - $plugin->getCreated(), false) . ' ago
                </td>
                <td>
                    <form action="" method="POST">
                        <input type="hidden" name="author" value="' . $authorID . '" />
                        <input type="hidden" name="plugin" value="' . $plugin->getID() . '" />
                        <input type="hidden" name="email" value="' . htmlentities($email) . '" />
                        <input type="submit" name="submit" class="btn btn-success" value="Accept"/>
                    </form>
                </td>
                <td>
                    <form action="" method="POST">
                        <input type="hidden" name="author" value="' . $authorID . '" />
                        <input type="hidden" name="plugin" value="' . $plugin->getID() . '" />
                        <input type="hidden" name="email" value="' . htmlentities($email) . '" />
                        <input type="submit" name="submit" class="btn btn-danger" value="Reject"/>
                    </form>
                </td>
            </tr>
';

}

echo '
        </tbody>
    </table>
</div>';


send_footer();