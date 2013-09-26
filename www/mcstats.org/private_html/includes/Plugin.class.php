<?php
if (!defined('ROOT')) {
    exit('For science.');
}

// Minimum countries required to display 'Others'
define('MINIMUM_FOR_OTHERS', 15);

class Plugin {
    /**
     * Internal id
     * @var integer
     */
    private $id;

    /**
     * The parent plugin
     * @var integer
     */
    private $parent;

    /**
     * The plugin's name
     * @var string
     */
    private $name;

    /**
     * The plugin's author
     * @var string
     */
    private $authors;

    /**
     * If the plugin is hidden from the main page
     * @var boolean
     */
    private $hidden;

    /**
     * The total amount of hits this plugin has received
     * @var integer
     */
    private $globalHits;

    /**
     * When the plugin was created
     * @var
     */
    private $created;

    /**
     * If the plugin is pending sufficient access. This is only set when obtained in context with an Author
     * (that is, get_accessible_plugins())
     * @var boolean
     */
    private $pendingAccess;

    /**
     * When the plugin was started on a server
     * @var
     */
    private $lastUpdated;

    /**
     * The plugin's rank
     * @var
     */
    private $rank;

    /**
     * The plugin's last rank
     * @var
     */
    private $lastRank;

    /**
     * The plugin's last rank change (unix epoch)
     * @var
     */
    private $lastRankChange;

    /**
     * Normalized server count for the last 24 hours
     * @var
     */
    private $serverCount = -1;

    /**
     * Cache of graph objects
     * @var Graph[]
     */
    private $graphCache = array();

    /**
     * Order the plugin's active graphs to have linear position arrangements. For example,
     * [ 1, 2, 5, 1543, 9000, 90001 ]
     * Where [ 1, 9000, 9001 ] are enforced graphs, it will become
     * [ 1, 2, 3, 4, 9000, 90001 ]
     */
    public function orderGraphs() {
        $graphs = $this->getActiveGraphs();

        $count = count($graphs);

        // do they even have any custom graphs ?
        if ($count == 3) {
            return;
        }

        $current = 2; // the position to use
        foreach ($graphs as $graph) {
            // ignore predefined graphs
            if ($graph->isReadOnly()) {
                continue;
            }

            $graph->setPosition($current++);
            $graph->save();
        }
    }

    /**
     * Get the key is prefixed to entries stored in the cache
     * @return string
     */
    private function cacheKey() {
        return 'plugin-' . $this->id;
    }

    /**
     * Loads all active graphs from the database into the cache
     */
    public function preloadGraphs() {
        global $master_db_handle;

        $statement = $master_db_handle->prepare('SELECT Graph.ID, Graph.Type, Graph.Active, Graph.Readonly, Graph.Name, Graph.DisplayName, Graph.Scale, Graph.Halfwidth, Graph.Position, CustomColumn.ID as ColumnID, CustomColumn.Name as ColumnName FROM Graph LEFT OUTER JOIN CustomColumn ON Graph.ID = CustomColumn.Graph WHERE Graph.Plugin = ? AND Graph.Active = 1');
        $statement->execute(array($this->id));

        while ($row = $statement->fetch()) {
            if (!isset($this->graphCache[$row['Name']])) {
                $this->graphCache[$row['Name']] = new Graph($row['ID'], $this, $row['Type'], $row['Name'], $row['DisplayName'], $row['Active'], $row['Readonly'], $row['Position'], $row['Scale'], $row['Halfwidth'] == 1, false);
            }

            $graph = $this->graphCache[$row['Name']];

            if (isset($row['ColumnName']) && $row['ColumnName'] != null) {
                $graph->addLocalColumn($row['ColumnID'], $row['ColumnName']);
            }
        }
    }

    /**
     * Loads a graph from the database and if it does not exist, initialize an empty graph in the
     * database and return it
     *
     * @param $name
     * @return Graph
     */
    public function getOrCreateGraph($name, $attemptedToCreate = false, $active = 0, $type = GraphType::Line, $readonly = false, $position = 2, $halfwidth = false) {
        global $master_db_handle;

        if (isset($this->graphCache[$name])) {
            return $this->graphCache[$name];
        }

        // Try to get it from the database
        $statement = $master_db_handle->prepare('SELECT ID, Plugin, Type, Active, Readonly, Name, DisplayName, Scale, Halfwidth, Position FROM Graph WHERE Plugin = ? AND Name = ?');
        $statement->execute(array($this->id, $name));

        if ($row = $statement->fetch()) {
            $this->graphCache[$name] = new Graph($row['ID'], $this, $row['Type'], $row['Name'], $row['DisplayName'], $row['Active'], $row['Readonly'], $row['Position'], $row['Scale'], $row['Halfwidth'] == 1);
            return $this->graphCache[$name];
        }

        if ($attemptedToCreate) {
            error_fquit('Failed to create graph for "' . $name . '"');
        }

        $statement = $master_db_handle->prepare('INSERT INTO Graph (Plugin, Type, Name, DisplayName, Active, Readonly, Halfwidth, Position)
                                                            VALUES(:Plugin, :Type, :Name, :DisplayName, :Active, :Readonly, :Halfwidth, :Position)');
        $statement->execute(array(
            ':Plugin' => $this->id,
            ':Type' => $type,
            ':Name' => $name,
            ':DisplayName' => $name,
            ':Active' => $active,
            ':Readonly' => $readonly ? 1 : 0,
            ':Halfwidth' => $halfwidth ? 1 : 0,
            ':Position' => $position
        ));

        // reselect it
        return $this->getOrCreateGraph($name, true, $active, $readonly);
    }

    /**
     * Loads a graph from the database and if it does not exist return NULL
     *
     * @param $name
     * @return Graph The Graph object and if it does not exist, NULL
     */
    public function getGraphByName($name) {
        global $master_db_handle;

        if (isset($this->graphCache[$name])) {
            return $this->graphCache[$name];
        }

        // Try to get it from the database
        $statement = $master_db_handle->prepare('SELECT ID, Plugin, Type, Active, Readonly, Name, DisplayName, Scale, Halfwidth, Position FROM Graph WHERE Plugin = ? AND Name = ?');
        $statement->execute(array($this->id, $name));

        if ($row = $statement->fetch()) {
            $this->graphCache[$name] = new Graph($row['ID'], $this, $row['Type'], $row['Name'], $row['DisplayName'], $row['Active'], $row['Readonly'], $row['Position'], $row['Scale'], $row['Halfwidth'] == 1);
            return $this->graphCache[$name];
        }

        return null;
    }

    /**
     * Load a graph using its ID
     * @param $id integer
     * @return Graph if found, otherwise NULL
     */
    public function getGraph($id) {
        global $master_db_handle;

        foreach ($this->graphCache as $graphName => $graph) {
            if ($graph->getID() == $id) {
                return $graph;
            }
        }

        $statement = $master_db_handle->prepare('SELECT ID, Plugin, Type, Active, Readonly, Name, DisplayName, Scale, Halfwidth, Position FROM Graph WHERE ID = ?');
        $statement->execute(array($id));

        if ($row = $statement->fetch()) {
            $graph = new Graph($row['ID'], $this, $row['Type'], $row['Name'], $row['DisplayName'], $row['Active'], $row['Readonly'], $row['Position'], $row['Scale'], $row['Halfwidth'] == 1);
            $this->graphCache[$graph->getName()] = $graph;
            return $graph;
        }

        return null;
    }

    /**
     * Gets all of the active graphs for the plugin
     * @return Graph[]
     */
    public function getActiveGraphs() {
        global $master_db_handle;

        // The graphs to return
        $graphs = array();

        $statement = $master_db_handle->prepare('SELECT ID, Plugin, Type, Active, Readonly, Name, DisplayName, Scale, Halfwidth, Position FROM Graph WHERE Plugin = ? AND Active = 1 ORDER BY Position ASC');
        $statement->execute(array($this->id));

        while ($row = $statement->fetch()) {
            $graphs[] = new Graph($row['ID'], $this, $row['Type'], $row['Name'], $row['DisplayName'], $row['Active'], $row['Readonly'], $row['Position'], $row['Scale'], $row['Halfwidth'] == 1);
        }

        return $graphs;
    }

    /**
     * Gets all of the graphs for the plugin
     * @return Graph[]
     */
    public function getAllGraphs() {
        global $master_db_handle;

        // The graphs to return
        $graphs = array();

        $statement = $master_db_handle->prepare('SELECT ID, Plugin, Type, Active,Readonly,  Name, DisplayName, Scale, Halfwidth, Position FROM Graph WHERE Plugin = ? ORDER BY Active DESC, Position ASC');
        $statement->execute(array($this->id));

        while ($row = $statement->fetch()) {
            $graphs[] = new Graph($row['ID'], $this, $row['Type'], $row['Name'], $row['DisplayName'], $row['Active'], $row['Readonly'], $row['Position'], $row['Scale'], $row['Halfwidth'] == 1);
        }

        return $graphs;
    }

    /**
     * Set a key and value unique to this plugin into the caching daemon
     *
     * @param $key
     * @param $value
     * @param $expire Seconds to expire the cached value in. Defaults to the next caching interval
     * @return TRUE on success and FALSE on failure
     */
    public function cacheSet($key, $value, $expire = CACHE_UNTIL_NEXT_GRAPH) {
        global $cache;
        return $cache->set($this->cacheKey() . $key, $value, $expire);
    }

    /**
     * Get a key from the caching daemon
     *
     * @param $key
     * @return the object returned from the cache
     */
    public function cacheGet($key) {
        global $cache;
        return $cache->get($this->cacheKey() . $key);
    }

    /**
     * Get a server by its GUID. If not found, this will create it.
     * @param $guid
     * @param $attemptedToCreate
     */
    public function getOrCreateServer($guid, $attemptedToCreate = false) {
        global $master_db_handle;

        // Try to select it first
        $statement = get_slave_db_handle()->prepare('SELECT Server.ID, GUID, ServerVersion, Country, Hits, Created, ServerSoftware, MinecraftVersion, Players, Plugin, ServerPlugin.Version, ServerPlugin.Updated
                                                FROM Server
                                                LEFT OUTER JOIN ServerPlugin ON ServerPlugin.Server = Server.ID
                                                WHERE GUID = :GUID');
        $statement->execute(array(':GUID' => $guid));

        // The server object
        $server = null;

        while ($row = $statement->fetch()) {
            if ($server === null) {
                $server = new Server();
                $server->setID($row['ID']);
                $server->setPlugin($this->id);
                $server->setGUID($row['GUID']);
                $server->setCountry($row['Country']);
                $server->setPlayers($row['Players']);
                $server->setServerVersion($row['ServerVersion']);
                $server->setHits($row['Hits']);
                $server->setCreated($row['Created']);
                $server->setServerSoftware($row['ServerSoftware']);
                $server->setMinecraftVersion($row['MinecraftVersion']);
                $server->setModified(false);
            }

            if ($row['Plugin'] == $this->id) {
                $server->setCurrentVersion($row['Version']);
                $server->setUpdated($row['Updated']);
                $server->setModified(false);
                return $server;
            }
        }

        // Do we need to add the plugin?
        if ($server !== null) {
            $statement = $master_db_handle->prepare('INSERT INTO ServerPlugin (Server, Plugin, Version, Updated) VALUES (:Server, :Plugin, :Version, :Updated)');
            $statement->execute(array(':Server' => $server->getID(), ':Plugin' => $this->id, ':Version' => '', ':Updated' => time()));

            $server->setUpdated(time());
            $server->setModified(false);

            // Return the server object
            return $server;
        }

        // Did we already try to create it?
        if ($attemptedToCreate) {
            error_fquit($this->name . ': Failed to create server for "' . $guid . '"');
        }

        // It doesn't exist so we are going to create it ^^
        $statement = $master_db_handle->prepare('INSERT INTO Server (GUID, Players, Country, ServerVersion, Hits, Created) VALUES(:GUID, :Players, :Country, :ServerVersion, :Hits, :Created)');
        $statement->execute(array(':GUID' => $guid, ':Players' => 0, ':Country' => 'ZZ', ':ServerVersion' => '', ':Hits' => 0, ':Created' => time()));

        // reselect it
        return $this->getOrCreateServer($guid, true);
    }

    /**
     * Get an array of possible versions
     * @return array
     */
    public function getVersions() {
        $db_handle = get_slave_db_handle();

        $versions = array();
        $statement = $db_handle->prepare('SELECT ID, Version FROM Versions WHERE Plugin = ? ORDER BY Created DESC');
        $statement->execute(array($this->id));

        while (($row = $statement->fetch()) != null) {
            $versions[$row['ID']] = $row['Version'];
        }

        return $versions;
    }

    /**
     * Get all of the custom columns available for grapinh for this plugin
     * @return array, [id] => name
     * @deprecated
     */
    public function getCustomColumns() {
        $db_handle = get_slave_db_handle();

        $columns = array();
        $statement = $db_handle->prepare('SELECT ID, Name FROM CustomColumn WHERE Plugin = ?');
        $statement->execute(array($this->id));

        while (($row = $statement->fetch()) != null) {
            $id = $row['ID'];
            $name = $row['Name'];
            $columns[$id] = $name;
        }

        return $columns;
    }

    /**
     * Sum all of the data points for a custom column
     * @param $columnID int
     * @param $min int
     * @return int
     * @deprecated
     */
    public function sumCustomData($columnID, $min, $max = -1, $table = 'CustomData') {
        $db_handle = get_slave_db_handle();

        // use time() if $max is -1
        if ($max == -1) {
            $max = time();
        }

        $statement = $db_handle->prepare('SELECT SUM(DataPoint) FROM ' . $table . ' WHERE ColumnID = ? AND Plugin = ? AND Updated >= ? AND Updated <= ?');
        $statement->execute(array($columnID, $this->id, $min, $max));

        $row = $statement->fetch();
        return is_numeric($row[0]) ? $row[0] : 0;
    }

    /**
     * Cached custom data
     */
    private $customData = array();

    /**
     * Cached custom data
     */
    private $fullCustomData = array();

    /**
     * Get the custom timeline data for all times between the two epochs
     * @param $columnID int
     * @param $graph Graph
     * @return array keyed by the epoch
     */
    function getTimelineCustom($columnID, $minEpoch, $graph) {
        if (isset($this->fullCustomData[$minEpoch][$columnID])) {
            return $this->fullCustomData[$minEpoch][$columnID];
        }

        global $m_graphdata;

        $last = getLastGraphEpoch();

        // generate list of epoches to select from
        $epochPoints = array();
        $epoch = $minEpoch;

        while ($epoch < $last) {
            $epochPoints[] = intval($epoch);
            $epoch += 1800; // 30 mins
        }

        // generate list of columns to get
        $columnIds = array();

        foreach ($graph->getColumns() as $id => $name) {
            $columnIds[] = intval($id);
        }

        $cursor = $m_graphdata->find(array(
            'epoch' => array(
                '$gte' => $minEpoch
            ),
            'plugin' => intval($this->id),
            'graph' => intval($graph->getID())
        ));

        foreach ($columnIds as $id) {
            $this->fullCustomData[$minEpoch][$id] = array();
        }

        foreach ($cursor as $doc) {
            foreach ($doc['data'] as $column => $data) {
                if (isset($data['sum'])) {
                    $this->fullCustomData[$minEpoch][$column][$doc['epoch']] = $data['sum'];
                }
            }
        }

        foreach ($columnIds as $id) {
            ksort($this->fullCustomData[$minEpoch][$id]);
        }

        return $this->fullCustomData[$minEpoch][$columnID];
    }

    /**
     * Get the custom timeline data for the last graph that was generated
     * @param $columnID int
     * @param $graph Graph
     * @return array keyed by the epoch
     */
    function getTimelineCustomLast($columnID, $graph) {
        $epoch = getLastGraphEpoch();

        if (isset($this->customData[$epoch])) {
            return isset($this->customData[$epoch][$columnID]) ? $this->customData[$epoch][$columnID] : 0;
        }

        global $m_graphdata;

        $doc = $m_graphdata->findOne(array(
            'epoch' => intval($epoch),
            'plugin' => intval($this->id),
            'graph' => intval($graph->getID())
        ));

        $this->customData[$epoch] = array();

        foreach ($doc['data'] as $column => $data) {
            $this->customData[$epoch][$column] = $data['sum'];
        }

        return isset($this->customData[$epoch][$columnID]) ? $this->customData[$epoch][$columnID] : 0;
    }

    /**
     * Sum all of the current player counts for servers that have pinged the server in the last hour
     * @param $after integer
     */
    public function sumPlayersOfServersLastUpdated($min, $max = -1) {
        $db_handle = get_slave_db_handle();

        // use time() if $max is -1
        if ($max == -1) {
            $max = time();
        }

        $statement = $db_handle->prepare('SELECT SUM(Players) FROM ServerPlugin LEFT OUTER JOIN Server ON Server.ID = ServerPlugin.Server WHERE ServerPlugin.Plugin = ? AND ServerPlugin.Updated >= ? AND ServerPlugin.Updated <= ?');
        $statement->execute(array($this->id, $min, $max));

        $row = $statement->fetch();
        return $row != null ? $row[0] : 0;
    }

    /**
     * Count version changes for epoch times between the min and max for the given version
     *
     * @param $version
     * @param $min
     * @param $max
     * @return integer
     */
    public function countVersionChanges($version, $min, $max = -1) {
        $db_handle = get_slave_db_handle();

        if ($max == -1) {
            $max = time();
        }

        $statement = $db_handle->prepare('SELECT COUNT(*) FROM VersionHistory WHERE Version = ? AND Created >= ? AND Created <= ?');
        $statement->execute(array($version, $min, $max));

        $row = $statement->fetch();
        return $row != null ? $row[0] : 0;
    }

    /**
     * Get a count of all of the servers using this plugin
     */
    public function countServers() {
        $db_handle = get_slave_db_handle();

        $statement = $db_handle->prepare('SELECT COUNT(*) FROM ServerPlugin WHERE Plugin = ?');
        $statement->execute(array($this->id));

        $row = $statement->fetch();
        return $row != null ? $row[0] : 0;
    }

    /**
     * Count all of the servers that were updated after the given epoch
     * @param $after integer
     */
    public function countServersLastUpdated($min) {
        $db_handle = get_slave_db_handle();

        if ($this->id == GLOBAL_PLUGIN_ID) {
            $statement = $db_handle->prepare('SELECT COUNT(distinct Server) FROM ServerPlugin WHERE Updated >= ?');
            $statement->execute(array($min));
        } else {
            $statement = $db_handle->prepare('SELECT COUNT(*) FROM ServerPlugin WHERE Plugin = ? AND Updated >= ?');
            $statement->execute(array($this->id, $min));
        }

        $row = $statement->fetch();
        return $row != null ? $row[0] : 0;
    }

    /**
     * Count all of the servers that were updated after the given epoch
     * @param $after integer
     */
    public function countServersLastUpdatedFromCountry($country, $min, $max = -1) {
        $db_handle = get_slave_db_handle();

        // use time() if $max is -1
        if ($max == -1) {
            $max = time();
        }

        $statement = $db_handle->prepare('SELECT COUNT(*) FROM ServerPlugin
                                    LEFT OUTER JOIN Server ON (ServerPlugin.Server = Server.ID)
                                    WHERE Country = ? AND ServerPlugin.Plugin = ? AND ServerPlugin.Updated >= ? AND ServerPlugin.Updated <= ?');
        $statement->execute(array($country, $this->id, $min, $max));

        $row = $statement->fetch();
        return $row != null ? $row[0] : 0;
    }

    public function countServersUsingVersion($version) {
        $db_handle = get_slave_db_handle();
        $weekAgo = time() - SECONDS_IN_DAY;

        $statement = $db_handle->prepare('SELECT COUNT(*) FROM ServerPlugin WHERE Plugin = ? AND Version = ? AND Updated >= ?');
        $statement->execute(array($this->id, $version, $weekAgo));

        $row = $statement->fetch();
        return $row != null ? $row[0] : 0;
    }

    /**
     * Get country timeline data between two epochs, showing the amount of players online per country
     * @param $minEpoch int
     * @param $maxEpoch int
     * @return array keyed by the epoch
     */
    function getTimelineCountry($minEpoch, $maxEpoch = -1) {
        $db_handle = get_slave_db_handle();

        // use time() if $max is -1
        if ($maxEpoch == -1) {
            $maxEpoch = time();
        }

        $ret = array();

        $statement = $db_handle->prepare('SELECT Country, Servers, Epoch FROM CountryTimeline WHERE Plugin = ? AND Epoch >= ? AND Epoch <= ? ORDER BY Epoch DESC');
        $statement->execute(array($this->id, $minEpoch, $maxEpoch));

        while ($row = $statement->fetch()) {
            $ret[$row['Epoch']][$row['Country']] = $row['Servers'];
        }

        return $ret;
    }

    /**
     * Gets the last set of country timelines
     * @param $minEpoch int
     * @param $maxEpoch int
     * @return array keyed by the epoch
     */
    function getTimelineCountryLast() {
        $db_handle = get_slave_db_handle();

        $ret = array();

        $epoch = getLastGraphEpoch();
        $statement = $db_handle->prepare('SELECT Country, Servers FROM CountryTimeline WHERE Plugin = ? AND Epoch = ?');
        $statement->execute(array($this->id, $epoch));

        while ($row = $statement->fetch()) {
            $country = $row['Country'];
            $servers = $row['Servers'];

            $ret[$epoch][$country] = $servers;
        }

        return $ret;
    }

    /**
     * Get the amount of players online between two epochs
     * @param $minEpoch int
     * @param $maxEpoch int
     * @return array keyed by the epoch
     */
    function getTimelinePlayers($minEpoch, $maxEpoch = -1) {
        $db_handle = get_slave_db_handle();

        // use time() if $max is -1
        if ($maxEpoch == -1) {
            $maxEpoch = time();
        }

        $ret = array();

        $statement = $db_handle->prepare('SELECT Players, Epoch FROM PlayerTimeline WHERE Plugin = ? AND Epoch >= ? AND Epoch <= ? ORDER BY Epoch ASC');
        $statement->execute(array($this->id, $minEpoch, $maxEpoch));

        while ($row = $statement->fetch()) {
            $ret[$row['Epoch']] = $row['Players'];
        }

        return $ret;
    }

    /**
     * Get the amount of servers online and using the plugin between two epochs
     * @param $minEpoch int
     * @param $maxEpoch int
     * @return array keyed by the epoch
     */
    function getTimelineServers($minEpoch, $maxEpoch = -1) {
        $db_handle = get_slave_db_handle();

        // use time() if $max is -1
        if ($maxEpoch == -1) {
            $maxEpoch = time();
        }

        $ret = array();

        $statement = $db_handle->prepare('SELECT Servers, Epoch FROM ServerTimeline WHERE Plugin = ? AND Epoch >= ? AND Epoch <= ? ORDER BY Epoch ASC');
        $statement->execute(array($this->id, $minEpoch, $maxEpoch));

        while ($row = $statement->fetch()) {
            $ret[$row['Epoch']] = $row['Servers'];
        }

        return $ret;
    }

    /**
     * Get the amount of servers that used a given version between the given epochs
     * @param $minEpoch int
     * @param $maxEpoch int
     * @return array keyed by the epoch
     */
    function getTimelineVersion($versionID, $minEpoch, $maxEpoch = -1) {
        $db_handle = get_slave_db_handle();

        // use time() if $max is -1
        if ($maxEpoch == -1) {
            $maxEpoch = time();
        }

        $ret = array();

        $statement = $db_handle->prepare('SELECT Count, Epoch FROM VersionTimeline WHERE Plugin = ? AND Version = ? AND Epoch >= ? AND Epoch <= ? ORDER BY Epoch ASC');
        $statement->execute(array($this->id, $versionID, $minEpoch, $maxEpoch));

        while ($row = $statement->fetch()) {
            $ret[$row['Epoch']] = $row['Count'];
        }

        return $ret;
    }

    /**
     * Create the plugin in the database
     */
    public function create() {
        global $master_db_handle;

        // Prepare it
        $statement = $master_db_handle->prepare('INSERT INTO Plugin (Name, Author, Hidden, GlobalHits, Created) VALUES (:Name, :Author, :Hidden, :GlobalHits, UNIX_TIMESTAMP())');

        // Execute
        $statement->execute(array(':Name' => $this->name, ':Author' => $this->authors, ':Hidden' => $this->hidden,
            ':GlobalHits' => $this->globalHits));
    }

    /**
     * Save the plugin to the database
     */
    public function save() {
        global $master_db_handle;

        // Prepare it
        $statement = $master_db_handle->prepare('UPDATE Plugin SET Name = :Name, Author = :Author, Hidden = :Hidden, GlobalHits = :GlobalHits, Created = :Created, LastUpdated = :LastUpdated, Rank = :Rank, LastRank = :LastRank, LastRankChange = :LastRankChange, ServerCount30 = :ServerCount30 WHERE ID = :ID');

        // Execute
        $statement->execute(array(
            ':ID' => $this->id,
            ':Name' => $this->name,
            ':Author' => $this->authors,
            ':Hidden' => $this->hidden,
            ':GlobalHits' => $this->globalHits,
            ':Created' => $this->created,
            ':LastUpdated' => $this->lastUpdated,
            ':Rank' => $this->rank,
            ':LastRank' => $this->lastRank,
            ':LastRankChange' => $this->lastRankChange,
            ':ServerCount30' => $this->serverCount
        ));
    }

    /**
     * Increment the global hits for the plugin and save
     */
    public function incrementGlobalHits() {
        $this->globalHits += 1;
        $this->save();
    }

    public function getID() {
        return $this->id;
    }

    public function setID($id) {
        $this->id = $id;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getAuthors() {
        return $this->authors;
    }

    public function setAuthors($author) {
        $this->authors = $author;
    }

    public function isHidden() {
        return $this->hidden;
    }

    public function setHidden($hidden) {
        $this->hidden = $hidden;
    }

    public function getGlobalHits() {
        return $this->globalHits;
    }

    public function setGlobalHits($globalHits) {
        $this->globalHits = $globalHits;
    }

    public function getParent() {
        return $this->parent;
    }

    public function setParent($parent) {
        $this->parent = $parent;
    }

    /**
     * @return
     */
    public function getCreated() {
        return $this->created;
    }

    /**
     * @param  $created
     */
    public function setCreated($created) {
        $this->created = $created;
    }

    /**
     * @return boolean
     */
    public function getPendingAccess() {
        return $this->pendingAccess;
    }

    /**
     * @param boolean $pendingAccess
     */
    public function setPendingAccess($pendingAccess) {
        $this->pendingAccess = $pendingAccess;
    }

    /**
     * @return
     */
    public function getServerCount() {
        if ($this->serverCount == -1) {
            $this->serverCount = $this->countServersLastUpdated(normalizeTime() - SECONDS_IN_DAY);
        }

        return $this->serverCount;
    }

    /**
     * @param  $serverCount
     */
    public function setServerCount($serverCount) {
        $this->serverCount = $serverCount;
    }

    /**
     * @return
     */
    public function getLastUpdated() {
        return $this->lastUpdated;
    }

    /**
     * @param  $lastUpdated
     */
    public function setLastUpdated($lastUpdated) {
        $this->lastUpdated = $lastUpdated;
    }

    /**
     * @return
     */
    public function getRank() {
        return $this->rank;
    }

    /**
     * @param  $rank
     */
    public function setRank($rank) {
        $this->rank = $rank;
    }

    /**
     * @return
     */
    public function getLastRank() {
        return $this->lastRank;
    }

    /**
     * @param  $lastRank
     */
    public function setLastRank($lastRank) {
        $this->lastRank = $lastRank;
    }

    /**
     * @return
     */
    public function getLastRankChange() {
        return $this->lastRankChange;
    }

    /**
     * @param  $lastRankChange
     */
    public function setLastRankChange($lastRankChange) {
        $this->lastRankChange = $lastRankChange;
    }

}