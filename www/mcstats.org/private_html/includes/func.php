<?php
if (!defined('ROOT')) exit('For science.');

// Include classes
require 'Server.class.php';
require 'Plugin.class.php';
require 'DataGenerator.class.php';
require 'Cache.class.php';

// graphing libs
require 'Graph.class.php';
require 'highroller/HighRoller.php';
require 'highroller/HighRollerSeriesData.php';
require 'highroller/HighRollerSplineChart.php';
require 'highroller/HighRollerAreaSplineChart.php';
require 'highroller/HighRollerColumnChart.php';
require 'highroller/HighRollerPieChart.php';

// Some constants
define('SECONDS_IN_HOUR', 60 * 60);
define('SECONDS_IN_HALFDAY', 60 * 60 * 12);
define('SECONDS_IN_DAY', 60 * 60 * 24);
define('SECONDS_IN_WEEK', 60 * 60 * 24 * 7);

// plugin list
define('PLUGIN_LIST_RESULTS_PER_PAGE', 30);

// Global plugin ID, used to store global stats so
// we can easily re-use our own methods
define('GLOBAL_PLUGIN_ID', -1);

// Connect to the caching daemon
$cache = new Cache();
$cache->connect();

/**
 * Utility function for generating graphs
 *
 * @param $plugin
 * @param $columnName
 * @param $epoch
 * @param $sum
 * @param $count
 * @param $avg
 * @param $max
 * @param $min
 * @param $variance
 * @param $stddev
 */
function insertGraphDataScratch($graph, $plugin, $columnName, $epoch, $sum, $count, $avg, $max, $min, $variance, $stddev) {
    global $master_db_handle;

    // these can be NULL IFF there is only one data point (e.g one server) in the sample
    // we're using sample functions NOT population so this should be fairly obvious why
    // this will return null
    if ($variance === null || $stddev === null) {
        $variance = 0;
        $stddev = 0;
    }

    $insert = $master_db_handle->prepare('INSERT INTO GraphDataScratch (Plugin, ColumnID, Sum, Count, Avg, Max, Min, Variance, StdDev, Epoch)
                                                    VALUES (:Plugin, :ColumnID, :Sum, :Count, :Avg, :Max, :Min, :Variance, :StdDev, :Epoch)');
    $insert->execute(array(
        ':Plugin' => $plugin,
        ':ColumnID' => $graph->getColumnID($columnName),
        ':Epoch' => $epoch,
        ':Sum' => $sum,
        ':Count' => $count,
        ':Avg' => $avg,
        ':Max' => $max,
        ':Min' => $min,
        ':Variance' => $variance,
        ':StdDev' => $stddev
    ));
}

/**
 * Get the graph generator's generation percentage. This can return NULL which means generation is not currently
 * happening.
 *
 * @return the percent complete of generation. If NULL the generator is not currently running
 */
function graph_generator_percentage()
{
    $path = ROOT . '../generator.txt';

    if (!file_exists($path))
    {
        return NULL;
    }

    $handle = fopen($path, 'r');

    if ($handle === FALSE)
    {
        return NULL;
    }

    // percent is only ever at most 3 bytes so only read that
    $percent = trim(fread($handle, 3));

    // close it
    fclose($handle);
    return $percent;
}

/**
 * Output all of the graphs for a given plugin
 * @param $plugin
 */
function outputGraphs($plugin)
{
    /// Load all of the custom graphs for the plugin
    $activeGraphs = $plugin->getActiveGraphs();

    // the 3 graphs to combine on the same row
    // these graphs are expected to be together in similar order even if they are mixed up
    $combineGraphs = array(
        'Java Version', 'Operating System',
        'System Arch', 'System Cores',
        'Game Version', 'Server Software'
    );

    /// Output a div for each one
    $index = 1;
    $floated = FALSE;
    foreach ($activeGraphs as $activeGraph)
    {
        // TODO not hardcoded ? heh
        $activeGraph->setFeedURL(sprintf('https://mcstats.org/api/1.0/%s/graph/%s', urlencode(htmlentities($plugin->getName())), urlencode(htmlentities($activeGraph->getName()))));

        $safeHTMLName = htmlentities($activeGraph->getDisplayName());
        $safeName = urlencode($safeHTMLName);

        $height = '450px';
        if ($activeGraph->getType() == GraphType::Pie || $activeGraph->getType() == GraphType::Donut)
        {
            $height = '400px';
        }

        $jsLoader = 'retrieveGraphData(CustomChart' . $index . 'Options, ' . ($activeGraph->getHighstocksClassName() == 'highcharts' ? 'HIGHCHARTS' : 'HIGHSTOCKS') . ', "' . $activeGraph->getFeedURL() . '");';
        if (in_array($activeGraph->getName(), $combineGraphs))
        {
            $reset = FALSE;
            if (!$floated)
            {
                $floated = TRUE;
                echo '<div class="row-fluid">';
            } else
            {
                $reset = TRUE;
            }
            echo <<<END
                        <div class="span6">
                            <div class="widget-box">
                                <a id="$safeName"></a>
                                <div class="widget-title"><span class="icon"><i class="icon-signal"></i></span><h5><a href="#$safeName">$safeHTMLName</a></h5><div class="buttons"><a href="javascript:void;" class="btn btn-mini" onclick='$jsLoader'><i class="icon-refresh"></i> Update stats</a></div></div>
                                <div class="widget-content">
                                    <div id="CustomChart$index" style="height: $height;"></div>
                                </div>
                            </div>
                        </div>

END;
            if ($reset)
            {
                echo '</div>';
                $floated = FALSE;
            }
        } else
        {
            if ($floated)
            {
                echo '</div>';
            }

            echo <<<END
                        <div class="row-fluid widget-box">
                            <a id="$safeName"></a>
                            <div class="widget-title"><span class="icon"><i class="icon-signal"></i></span><h5><a href="#$safeName">$safeHTMLName</a></h5><div class="buttons"><a href="javascript:void;" class="btn btn-mini" onclick='$jsLoader'><i class="icon-refresh"></i> Update stats</a></div></div>
                            <div class="widget-content">
                                <div id="CustomChart$index" style="height: $height;"></div>
                            </div>
                        </div>

END;

        }

        $index ++;
    }

    /// Flush before sending / generating graph data
    flush();

    /// MULTIPLE CUSTOM GRAPHS YEAH TO THE POWER OF FUCK YEAH
    // ITERATE THROUGH THE ACTIVE GRAPHS
    $index = 1; // WE GIVE A UNIQUE NUMBER TO EACH CHART
    foreach ($activeGraphs as $activeGraph)
    {
        // ADD ALL OF THE SERIES PLOTS TO THE CHART
        if ($activeGraph->getType() != GraphType::Pie && $activeGraph->getType() != GraphType::Donut)
        {
            foreach ($activeGraph->getColumns() as $id => $columnName)
            {
                // GENERATE SOME DATA DIRECTLY TO THE CHART!
                $series = new HighRollerSeriesData();
                // $activeGraph->addSeries($series->addName($columnName)->addData(DataGenerator::generateCustomChartData($activeGraph, $id)));
            }
            $series = new HighRollerSeriesData();
            $activeGraph->addSeries($series);
        } else // Pie chart
        {
            // Finalize
            // $activeGraph->addSeries($series->addName('')->addData($seriesData));
            $series = new HighRollerSeriesData();
            $activeGraph->addSeries($series);
        }

        // GENERATE THE GRAPH, OH HELL YEAH!
        echo '<script type="text/javascript">' . $activeGraph->generateGraph('CustomChart' . $index++) . '</script>';
        flush();
    }
}

/**
 * Log an error and force end the process
 * @param $message
 */
function error_fquit($message)
{
    if (PHP_SAPI == 'cli')
    {
        echo $message . PHP_EOL;
    } else
    {
        error_log($message);
        exit;
    }
}

/**
 * Gets seconds since crons last ran
 * @return integer
 */
function getTimeLast()
{
    $timelast = -1;
    $statement = get_slave_db_handle()->prepare('SELECT UNIX_TIMESTAMP(NOW()) - MAX(Epoch) FROM GraphData');
    $statement->execute();
    if ($row = $statement->fetch()) $timelast = (int)$row[0];
    // max 2 hours
    if($timelast > 7200) $timelast = 0;
    return($timelast);
}

/**
 * Get the epoch of the last graph that was generated
 * @return int
 */
function getLastGraphEpoch()
{
    $statement = get_slave_db_handle()->prepare('SELECT MAX(Epoch) FROM GraphData');
    $statement->execute();
    $row = $statement->fetch();
    return $row != null ? $row[0] : 0;
}

/**
 * Checks a PDO statement for errors and if any exist, the script will exist and log to the error log
 *
 * @param $statement PDOStatement
 */
function check_statement($statement)
{
    $errorInfo = $statement->errorInfo();

    // If the first element is 0, it's good
    if ($errorInfo[0] == 0)
    {
        return;
    }

    // Some error has occurred, log it and quit
    error_fquit('FQUIT Statement \"' . $statement->queryString . '" errorInfo() => ' . print_r($errorInfo, true));
}

/**
 * Get the epoch of the closest hour (downwards, never up)
 * @return float
 */
function getLastHour()
{
    return strtotime(date('F d Y H:00'));
}

/**
 * Calculate the time until the next graph will be calculated
 * @return int the unix timestamp of the next graph
 */
function timeUntilNextGraph()
{
    global $config;

    $interval = $config['graph']['interval'];
    return normalizeTime() + ($interval * 60);
}

/**
 * Normalize a time to the nearest graphing period
 *
 * @param $time if < 0, the time() will be used
 */
function normalizeTime($time = -1)
{
    global $config;

    if ($time < 0)
    {
        $time = time();
    }

    // The amount of minutes between graphing periods
    $interval = $config['graph']['interval'];

    // Calculate the denominator (interval * 60 secs)
    $denom = $interval * 60;

    // Round to the closest one
    return round(($time - ($denom / 2)) / $denom) * $denom;
}

/**
 * Sum the amount of servers that have reported since the last update
 * @return int
 */
function sumServersSinceLastUpdated()
{
    $baseEpoch = normalizeTime();
    $minimum = strtotime('-30 minutes', $baseEpoch);
    $statement = get_slave_db_handle()->prepare('select COUNT(distinct Server) AS Count from ServerPlugin where Updated >= ?');
    $statement->execute(array($minimum));

    if ($row = $statement->fetch())
    {
        return $row['Count'];
    }

    return 0;
}

/**
 * Sum the amount of players that have reported since the last update
 * @return int
 */
function sumPlayersSinceLastUpdated()
{
    $baseEpoch = normalizeTime();
    $minimum = strtotime('-30 minutes', $baseEpoch);
    $statement = get_slave_db_handle()->prepare('SELECT SUM(dev.Players) AS Count FROM (SELECT DISTINCT Server, Server.Players from ServerPlugin LEFT OUTER JOIN Server ON Server.ID = ServerPlugin.Server WHERE ServerPlugin.Updated >= ?) dev;');
    $statement->execute(array($minimum));

    if ($row = $statement->fetch())
    {
        return $row['Count'];
    }

    return 0;
}

/**
 * Load a key from POST. If it does not exist, die loudly
 *
 * @param $key string
 * @return string
 */
function getPostArgument($key)
{
    // FIXME change to $_POST
    // check
    if (!isset($_POST[$key]))
    {
        if (PHP_SAPI == 'cli')
        {
            return NULL;
        } else
        {
            exit('ERR Missing arguments');
        }
    }

    return $_POST[$key];
}

/**
 * Extract custom data from the post request. Used in R5 and above
 * Array format:
 * {
 *      "GraphName": {
 *          "ColumnName": Value
 *      },
 *      ...
 * }
 * @return array
 */
function extractCustomData()
{
    global $config;
    $start = millitime();

    // What custom data is separated by
    $separator = $config['graph']['separator'];

    // Array of data to return
    $data = array();

    foreach ($_POST as $key => $value)
    {
        // verify we have a number as the key
        if (!is_numeric($value)) {
            continue;
        }

        // Find the first position of the separator
        $r_index = strrpos($key, $separator);

        // Did we not match one?
        if ($r_index === FALSE)
        {
            continue;
        }

        // Extract the data :-)
        $graphName = str_replace('_', ' ', substr($key, 3, $r_index - 3));
        $columnName = str_replace('_', ' ', substr($key, $r_index + 2));

        // Set it :-)
        $data[$graphName][$columnName] = $value;
    }

    return $data;
}

/**
 * Extract custom data from the post request. Used in R4 and lower.
 * Array format:
 * {
 *      "ColumnName": Value,
 *      ...
 * }
 *
 * @return array
 */
function extractCustomDataLegacy()
{
    $custom = array();

    foreach ($_POST as $key => $value)
    {
        // verify we have a number as the key
        if (!is_numeric($value)) {
            continue;
        }

        // check if the string starts with custom
        // note !== note == (false == 0, false !== 0)
        if (stripos($key, 'custom') !== 0) {
            continue;
        }

        $columnName = str_replace('_', ' ', substr($key, 6));
        $columnName = mb_convert_encoding($columnName, 'ISO-8859-1', 'UTF-8');

        if (strstr($columnName, 'Protections') !== FALSE)
        {
            $columnName = str_replace('?', 'i', $columnName);
        }

        if (!in_array($columnName, $custom))
        {
            $custom[$columnName] = $value;
        }
    }

    return $custom;
}

/**
 * Get all of the possible country codes we have stored
 *
 * @return string[], e.g ["CA"] = "Canada"
 */
function loadCountries()
{
    $countries = array();

    $statement = get_slave_db_handle()->prepare('SELECT ShortCode, FullName FROM Country LIMIT 300'); // hard limit of 300
    $statement->execute();

    while ($row = $statement->fetch())
    {
        $shortCode = $row['ShortCode'];
        $fullName = $row['FullName'];

        $countries[$shortCode] = $fullName;
    }

    return $countries;
}

/**
 * Resolve a plugin object from a row
 *
 * @param $row
 * @return Plugin
 */
function resolvePlugin($row)
{
    $plugin = new Plugin();
    $plugin->setID($row['ID']);
    $plugin->setParent($row['Parent']);
    $plugin->setName($row['Name']);
    $plugin->setAuthors($row['Author']);
    $plugin->setHidden($row['Hidden']);
    $plugin->setGlobalHits($row['GlobalHits']);
    $plugin->setCreated($row['Created']);
    $plugin->setLastUpdated($row['LastUpdated']);
    $plugin->setRank($row['Rank']);
    $plugin->setLastRank($row['LastRank']);
    $plugin->setLastRankChange($row['LastRankChange']);
    $plugin->setServerCount($row['ServerCount30']);

    return $plugin;
}

define ('PLUGIN_ORDER_ALPHABETICAL', 1);
define ('PLUGIN_ORDER_POPULARITY', 2);
define ('PLUGIN_ORDER_RANDOM', 3);
define ('PLUGIN_ORDER_RANDOM_TOP100', 4);

/**
 * Count the number of plugins in the database
 * @param int $order the order / ruleset to count
 */
function countPlugins($order = PLUGIN_ORDER_POPULARITY)
{
    $db_handle = get_slave_db_handle();

    switch ($order)
    {
        case PLUGIN_ORDER_ALPHABETICAL:
            $query = 'SELECT * FROM Plugin WHERE Parent = -1';
            break;

        case PLUGIN_ORDER_POPULARITY:
            $query = 'SELECT * FROM Plugin WHERE Plugin.Parent = -1';
            break;

        case PLUGIN_ORDER_RANDOM:
            $query = 'SELECT * FROM Plugin WHERE Parent = -1';
            break;

        case PLUGIN_ORDER_RANDOM_TOP100:
            $query = 'SELECT * FROM Plugin WHERE Parent = -1 AND Rank <= 100';
            break;

        default:
            error_log ('Unimplemented loadPlugins () order => ' . $order);
            exit('Unimplemented loadPlugins () order => ' . $order);
    }

    $statement = $db_handle->prepare($query);
    $statement->execute(array(normalizeTime() - SECONDS_IN_DAY));

    $pluginCount = 0;
    while ($row = $statement->fetch())
    {
        $pluginCount ++;
    }
    return $pluginCount;
}

/**
 * Loads all of the plugins from the database
 *
 * @return Plugin[]
 */
function loadPlugins($order = PLUGIN_ORDER_POPULARITY, $limit = -1, $start = -1)
{
    $db_handle = get_slave_db_handle();
    $plugins = array();

    switch ($order)
    {
        case PLUGIN_ORDER_ALPHABETICAL:
            $query = 'SELECT ID, Parent, Name, Author, Hidden, GlobalHits, Created, Rank, LastRank, LastRankChange, LastUpdated, ServerCount30 FROM Plugin WHERE Parent = -1 ORDER BY Name ASC';
            break;

        case PLUGIN_ORDER_POPULARITY:
            $query = 'SELECT Plugin.ID, Parent, Name, Author, Hidden, GlobalHits, Created, Rank, LastRank, LastRankChange, LastUpdated, ServerCount30 FROM Plugin WHERE LastUpdated >= ? AND Plugin.Parent = -1 AND Rank IS NOT NULL ORDER BY Rank ASC';
            break;

        case PLUGIN_ORDER_RANDOM:
            $query = 'SELECT ID, Parent, Name, Author, Hidden, GlobalHits, Created, Rank, LastRank, LastRankChange, LastUpdated, ServerCount30 FROM Plugin WHERE Parent = -1 ORDER BY RAND()';
            break;

        case PLUGIN_ORDER_RANDOM_TOP100:
            $query = 'SELECT ID, Parent, Name, Author, Hidden, GlobalHits, Created, Rank, LastRank, LastRankChange, LastUpdated, ServerCount30 FROM Plugin WHERE Parent = -1 AND Rank <= 100 ORDER BY RAND()';
            break;

        default:
            error_log ('Unimplemented loadPlugins () order => ' . $order);
            exit('Unimplemented loadPlugins () order => ' . $order);
    }

    if ($start != -1 && is_numeric($start))
    {
        $query .= ' LIMIT ' . $start . ',' . $limit;
    } else if ($limit != -1 && is_numeric($limit))
    {
        $query .= ' LIMIT ' . $limit;
    }

    $statement = $db_handle->prepare($query);
    $statement->execute(array(normalizeTime() - SECONDS_IN_DAY));

    while ($row = $statement->fetch())
    {
        $plugins[] = resolvePlugin($row);
    }

    return $plugins;
}

/**
 * Load a plugin
 *
 * @param $plugin string The plugin's name
 * @return Plugin if it exists otherwise NULL
 */
function loadPlugin($plugin)
{
    $statement = get_slave_db_handle()->prepare('SELECT ID, Parent, Name, Author, Hidden, GlobalHits, Created, Rank, LastRank, LastRankChange, LastUpdated, ServerCount30 FROM Plugin WHERE Name = :Name');
    $statement->execute(array(':Name' => $plugin));

    if ($row = $statement->fetch())
    {
        $plugin = resolvePlugin($row);

        // check for parent
        if ($plugin->getParent() != -1)
        {
            $parent = loadPluginByID($plugin->getParent());

            if ($parent != null)
            {
                return $parent;
            }
        }

        return $plugin;
    }

    return NULL;
}

// array of plugin objects
$plugins = array();

/**
 * Load a plugin using its internal ID
 *
 * @param $plugin integer
 * @return Plugin if it exists otherwise NULL
 */
function loadPluginByID($id)
{
    global $plugins;

    if (isset($plugins[$id])) {
        return $plugins[$id];
    }

    $statement = get_slave_db_handle()->prepare('SELECT ID, Parent, Name, Author, Hidden, GlobalHits, Created, Rank, LastRank, LastRankChange, LastUpdated, ServerCount30 FROM Plugin WHERE ID = :ID');
    $statement->execute(array(':ID' => $id));

    if ($row = $statement->fetch())
    {
        $plugins[$id] = resolvePlugin($row);
        return $plugins[$id];
    }

    return NULL;
}

/////////////////////////////////
/// User interface functions  ///
/////////////////////////////////

/**
 * Checks if a string ends with the given string
 *
 * @param $needle
 * @param $haystack
 * @return bool TRUE if the haystack ends with the given needle
 */
function str_endswith($needle, $haystack)
{
    return strrpos($haystack, $needle) === strlen($haystack)-strlen($needle);
}

/**
 * Sender the header html file to the user
 */
function send_header()
{
    include ROOT . '../private_html/assets/template/header.php';
}

/**
 * Send the footer html file to the user
 */
function send_footer()
{
    include ROOT . '../private_html/assets/template/footer.php';
}


/////////////////////////////////
/// Admin interface functions ///
/////////////////////////////////

/**
 * Output a formatted error
 *
 * @param $msg the error to send
 */
function err($msg)
{
    echo '
    <div class="row-fluid" style="margin-left: 25%; text-align: center;">
        <div class="alert alert-error span6" style="width: 50%; padding-bottom: 0;">
            <p>
                ' . $msg . '
            </p>
        </div>
    </div>';
}

/**
 * Output a formatted success message
 *
 * @param $msg the error to send
 */
function success($msg)
{
    echo '
    <div class="row-fluid" style="margin-left: 25%; text-align: center;">
        <div class="alert alert-success span6" style="width: 50%; padding-bottom: 0;">
            <p>
                ' . $msg . '
            </p>
        </div>
    </div>';
}

/**
 * Check if the given plugin can be accessed.
 *
 * @param $plugin Plugin or string
 * @return TRUE if the player can administrate the plugin
 */
function can_admin_plugin($plugin)
{
    if ($plugin instanceof Plugin)
    {
        $plugin_obj = $plugin;
    } else if ($plugin instanceof string)
    {
        $plugin_obj = loadPlugin($plugin);
    }

    // is it null??
    if ($plugin_obj == null)
    {
        return FALSE;
    }

    // iterate through our accessible plugins
    foreach (get_accessible_plugins() as $a_plugin)
    {
        if ($a_plugin->getName() == $plugin_obj->getName())
        {
            return $a_plugin->getPendingAccess() !== TRUE;
        }
    }

    return FALSE;
}

/**
 * Get all of the plugins the currently logged in user can access
 *
 * @param $selectFromPendingPool If returned plugins can include plugins from the pending pool
 * @return array Plugin
 */
function get_accessible_plugins($selectFromPendingPool = TRUE)
{
    global $_SESSION , $master_db_handle;

    // The plugins we can access
    $plugins = array();

    // Make sure they are plugged in
    if (!is_loggedin())
    {
        return $plugins;
    }

    // Query for all of the plugins
    $statement = $master_db_handle->prepare('SELECT Plugin, ID, Name, Parent, Plugin.Author, Hidden, GlobalHits, Created, Pending, Rank, LastRank, LastRankChange, LastUpdated, ServerCount30 FROM AuthorACL LEFT OUTER JOIN Plugin ON Plugin.ID = Plugin WHERE AuthorACL.Author = ? ORDER BY Name ASC');
    $statement->execute(array($_SESSION['uid']));

    while ($row = $statement->fetch())
    {
        if ($selectFromPendingPool == FALSE && $row['Pending'] == 1)
        {
            continue;
        }

        $plugin = resolvePlugin($row);
        $plugin->setPendingAccess($row['Pending'] == 1);

        $plugins[] = $plugin;
    }

    return $plugins;
}

/**
 * Check a login if it is correct
 *
 * @param $username
 * @param $password
 * @return string their correct username if the login is correct, otherwise FALSE
 */
function check_login($username, $password)
{
    global $master_db_handle , $_SESSION;

    // Create the query
    $statement = $master_db_handle->prepare('SELECT ID, Name, Password FROM Author WHERE Name = ?');
    $statement->execute(array($username));

    if ($row = $statement->fetch())
    {
        $real_username = $row['Name'];
        $hashed_password = $row['Password'];

        // Verify the password
        if (sha1($password) != $hashed_password)
        {
            return FALSE;
        }

        // Set some stuff
        $_SESSION['uid'] = $row['ID'];

        // Authenticated
        return $real_username;
    }

    return FALSE;
}

/**
 * Check if the user is logged in
 * @return bool TRUE if the user is logged in
 */
function is_loggedin()
{
    global $_SESSION;
    return isset($_SESSION['loggedin']);
}

/**
 * Ensure the user is logged in
 */
function ensure_loggedin()
{
    global $_SESSION;

    if (!isset($_SESSION['loggedin']))
    {
        header('Location: /admin/login.php');
        exit;
    }
}


/**
 * Profiling
 */

function function_log($functionName, $elapsed, $desc = '')
{
    if (PHP_SAPI == 'cli')
    {
        echo " => $functionName: {$elapsed}ms" . ($desc == '' ? '' : " : $desc") . PHP_EOL;
    } else
    {
        error_log(" => $functionName: {$elapsed}ms" . ($desc == '' ? '' : " : $desc"));
    }
}

/**
 * Get the current time in milliseconds
 * @return long
 */
function millitime()
{
    $timeparts = explode(" ",microtime());
    return bcadd(($timeparts[0]*1000),bcmul($timeparts[1],1000));
}

/**
 * Converts a unix epoch to human string (e.g xx hours xx minutes xx seconds)
 * @param $seconds
 * @param $outputSeconds TRUE if seconds should be included in the output
 */
function epochToHumanString($epoch, $outputSeconds = TRUE)
{
    $seconds = $epoch;

    $days = round($seconds / 86400);
    $seconds -= $days * 86400;

    $hours = round($seconds / 3600);
    $seconds -= $hours * 3600;

    $minutes = round($seconds / 60);
    $seconds -= $minutes * 60;

    $ret = '';

    if ($days > 0) {
        $ret .= $days . ' day' . ($days == 1 ? '' : 's') . ' ';
    }

    if ($hours > 0) {
        $ret .= $hours . ' hour' . ($hours == 1 ? '' : 's') . ' ';
    }

    if ($minutes > 0) {
        $ret .= $minutes . ' minute' . ($minutes == 1 ? '' : 's') . ' ';
    }

    if ($seconds > 0 && $outputSeconds) {
        $ret .= $seconds . ' second' . ($seconds == 1 ? '' : 's') . ' ';
    }

    if ($ret == '') {
        $ret = 'less than a ' . ($outputSeconds ? 'second' : 'minute');
    }

    return $ret;
}

function insert_cache_headers()
{
    global $config;
    header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', getLastGraphEpoch() + (60 * $config['graph']['interval'])));
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s \G\M\T', getLastGraphEpoch()));

    if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
        if (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= getLastGraphEpoch() && graph_generator_percentage() === NULL) {
            header('HTTP/1.1 304 Not Modified');
            exit;
        }
    }
}