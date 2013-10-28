<?php
if (!defined('ROOT')) {
    exit('For science.');
}

/**
 * What type of graph to generate
 * Abstract is to prevent instantiation or inheritance
 */
abstract class GraphType {

    /**
     * A line graph
     */
    const Line = 0;

    /**
     * An area graph
     */
    const Area = 1;

    /**
     * Column graph
     */
    const Column = 2;

    /**
     * A pie graph
     */
    const Pie = 3;

    /**
     * A percentage area graph
     */
    const Percentage_Area = 4;

    /**
     * A stacked column graph
     */
    const Stacked_Column = 5;

    /**
     * Donut graph
     */
    const Donut = 6;

    /**
     * Geomap
     */
    const Map = 7;

    public static function toString($type) {
        switch ($type) {
            case GraphType::Line:
                return "Line";

            case GraphType::Area:
                return "Area";

            case GraphType::Column:
                return "Column";

            case GraphType::Pie:
                return "Pie";

            case GraphType::Percentage_Area:
                return "Percentage Area";

            case GraphType::Stacked_Column:
                return "Stacked Column";

            case GraphType::Donut:
                return "Donut";

            case GraphType::Map:
                return "Map";

            default:
                return 'UNDEFINED';
        }
    }

}

/**
 * The graph scale a graph should use
 */
abstract class GraphScale {

    /**
     * Linear graph scale
     */
    const Linear = 'linear';

    /**
     * Logarithmic graph scale
     */
    const Logarithmic = 'log';

}

/// TODO save()
class Graph {

    /**
     * The graph's internal id
     * @var integer
     */
    private $id;

    /**
     * The plugin this graph is for
     * @var Plugin
     */
    private $plugin;

    /**
     * The graph's name
     * @var string
     */
    private $name;

    /**
     * The graph's display name
     * @var string
     */
    private $displayName;

    /**
     * The type of graph to generate
     * @var GraphType
     */
    private $type;

    /**
     * If the graph is active
     * @var
     */
    private $active;

    /**
     * If the graph is read-only
     * @var bool
     */
    private $readonly;

    /**
     * The graph's position
     * @var int
     */
    private $position;

    /**
     * The graph's scale
     * @var string
     */
    private $scale;

    /**
     * If the graph is halfwidth or not
     * @var bool
     */
    private $halfwidth;

    /**
     * The columns present in this graph
     * @var array
     */
    private $columns = array();

    /**
     * An array of the series objects
     * @var HighRollerSeriesData[]
     */
    private $series = array();

    /**
     * The feed url that feeds JSON to the graph
     * @var string
     */
    private $feedURL = '';

    public function __construct($id = -1, $plugin = null, $type = GraphType::Line, $name = '', $displayName = '', $active = 0, $readonly = false, $position = 1, $scale = 'linear', $halfwidth = false, $preloadGraphs = true) {
        $this->id = $id;
        $this->plugin = $plugin;
        $this->type = $type;
        $this->name = $name;
        $this->displayName = $displayName;
        $this->readonly = $readonly;
        $this->position = $position;
        $this->active = $active;
        $this->scale = $scale;
        $this->halfwidth = $halfwidth;

        // If the display name is blank, use the internal name
        if ($displayName == '') {
            $this->displayName = $name;
        }

        if ($this->id >= 0 && $preloadGraphs) {
            // Load the columns present in the graph
            $this->loadColumns();
        }
    }

    public function isOfficial() {
        return $this->position == 1 || $this->position > 1000;
    }

    /**
     * Save the graph to the database
     */
    public function save() {
        global $master_db_handle;

        $statement = $master_db_handle->prepare('UPDATE Graph SET DisplayName = ?, Type = ?, Active = ?, Readonly = ?, Position = ?, Scale = ?, Halfwidth = ? WHERE ID = ?');
        $statement->execute(array($this->displayName, $this->type, $this->active, $this->readonly ? 1 : 0, $this->position, $this->scale, $this->halfwidth ? 1 : 0, $this->id)); // TODO
    }

    /**
     * Add some custom data to the graph
     *
     * @param $server Server
     * @param $columnName string
     * @param $value int
     */
    public function addCustomData($server, $columnName, $value) {
        global $master_db_handle;

        // get the id for the column
        $columnID = $this->getColumnID($columnName);

        $statement = $master_db_handle->prepare('INSERT INTO CustomData (Server, Plugin, ColumnID, DataPoint, Updated) VALUES (:Server, :Plugin, :ColumnID, :DataPoint, :Updated)
                                    ON DUPLICATE KEY UPDATE DataPoint = VALUES(DataPoint) , Updated = VALUES(Updated)');
        $statement->bindValue(':Server', intval($server->getID()));
        $statement->bindValue(':Plugin', intval($this->plugin->getID()));
        $statement->bindValue(':ColumnID', intval($columnID));
        $statement->bindValue(':DataPoint', intval($value));
        $statement->bindValue(':Updated', time());
        $statement->execute();
    }

    /**
     * Get or create a custom column and return the id
     *
     * @param $columnName string
     * @return int
     */
    public function getColumnID($columnName) {
        global $master_db_handle;

        foreach ($this->columns as $id => $name) {
            if ($name == $columnName) {
                return $id;
            }
        }

        $statement = $master_db_handle->prepare('INSERT INTO CustomColumn (Plugin, Graph, Name) VALUES (:Plugin, :Graph, :Name)');
        $statement->execute(array(':Plugin' => $this->plugin->getID(), ':Graph' => $this->id, ':Name' => $columnName));

        // Now get the last inserted id
        $id = $master_db_handle->lastInsertId();
        $this->columns[$id] = $columnName;
        return $id;
    }

    /**
     * Get the highstocks class name to use
     * @return string
     */
    public function getHighstocksClassName() {
        switch ($this->type) {
            case GraphType::Pie:
            case GraphType::Donut:
                return 'highcharts';

            default:
                return 'highstock';
        }
    }

    /**
     * Generate the graph to be printed out onto the page.
     * The generated code should be placed inside <script> tags.
     * @return string javascript
     */
    public function generateGraph($renderTo) {
        // Only generate the graph if we have plotters
        if (count($this->series) == 0) {
            return;
        }

        // We need to create a chart using the type
        $chart = null;

        // The graphing classname to use
        $classname = $this->getHighstocksClassName();

        switch ($this->type) {
            case GraphType::Line:
                $chart = new HighRollerSplineChart();
                break;

            case GraphType::Area:
            case GraphType::Percentage_Area:
                $chart = new HighRollerAreaSplineChart();
                break;

            case GraphType::Column:
            case GraphType::Stacked_Column:
                $chart = new HighRollerColumnChart();
                break;

            case GraphType::Pie:
            case GraphType::Donut:
                $chart = new HighRollerPieChart();
                break;

            case GraphType::Map:
                $chart = new HighRollerSplineChart();
                $chart->chart->type = 'map';
                break;
        }

        // Nothing we can do if it's still null
        if ($chart === null) {
            return null;
        }

        // Set chart options
        $chart->feedurl = $this->feedURL;
        $chart->chart->renderTo = $renderTo;
        $chart->chart->zoomType = 'x';

        // The title
        $safeName = htmlentities($this->displayName);
        $chart->title->text = ' ';
        $chart->subtitle = array('text' => '');

        // Disable credits
        $chart->credits = array('enabled' => false);

        // Disable scrollbar
        $chart->scrollbar = array('enabled' => false);

        // Exporting options
        $chart->exporting = array(
            'enabled' => true,
            'filename' => str_replace(' ', '_', $this->getPlugin()->getName() . ' - ' . $this->getDisplayName())
        );

        // Non-pie graph specifics
        if ($this->type != GraphType::Pie && $this->type != GraphType::Donut) {
            $chart->rangeSelector = array(
                'selected' => (($this->type == GraphType::Column || $this->type == GraphType::Stacked_Column) ? 1 : 3),
                'buttons' => array(
                    array(
                        'type' => 'hour',
                        'count' => 2,
                        'text' => '2h'
                    ), array(
                        'type' => 'hour',
                        'count' => 12,
                        'text' => '12h'
                    ), array(
                        'type' => 'day',
                        'count' => 1,
                        'text' => '1d'
                    ), array(
                        'type' => 'week',
                        'count' => 1,
                        'text' => '1w'
                    ), array(
                        'type' => 'week',
                        'count' => 2,
                        'text' => '2w'
                    ), array(
                        'type' => 'month',
                        'count' => 1,
                        'text' => '1m'
                    )
                )
            );

            $chart->xAxis = array(
                'type' => 'datetime',
                'maxZoom' => 2 * 60,
                'dateTimeLabelFormats' => array(
                    'month' => '%e. %b',
                    'year' => '%b'
                ),
                'gridLineWidth' => 0
            );

            $chart->yAxis = array(
                'min' => 0,
                'title' => array('text' => ''),
                'labels' => array(
                    'align' => 'left',
                    'x' => 3,
                    'y' => 16
                ),
                'showFirstLabel' => false
            );

            // Should we make the graph log?
            if ($this->scale == GraphScale::Logarithmic) {
                $chart->yAxis['type'] = 'logarithmic';
                $chart->yAxis['minorTickInterval'] = 'auto';
                unset($chart->yAxis['min']);
            }
        }

        // Tooltip + plotOptions
        if ($this->type != GraphType::Pie && $this->type != GraphType::Donut) {
            $chart->tooltip = array(
                'shared' => true,
                'crosshairs' => true
            );

            if ($this->type == GraphType::Percentage_Area) {
                $chart->plotOptions = array(
                    'areaspline' => array(
                        'stacking' => 'percent'
                    )
                );
            } elseif ($this->type == GraphType::Stacked_Column) {
                $chart->plotOptions = array(
                    'column' => array(
                        'stacking' => 'normal'
                    )
                );
            }
        } else // Pie
        {
            $chart->plotOptions = array(
                'pie' => array(
                    'allowPointSelect' => true,
                    'cursor' => 'pointer'
                )
            );
        }

        // Add each series to the chart
        foreach ($this->series as $series) {
            $chart->addSeries($series);
        }

        // Some raw javascript
        $rawJavascript = '';

        if ($this->type != GraphType::Pie && $this->type != GraphType::Donut) {
            // just sorts the series
            $rawJavascript = "
                    ${renderTo}Options.tooltip =
                    {
                        \"shared\": true,
                        \"crosshairs\": true,
                        \"formatter\": function() {
                            var points = this.points;
                            var series = points[0].series;
                            var s = series.tooltipHeaderFormatter(points[0].key);

                            var sortedPoints = points.sort(function(a, b){
                                return ((a.y < b.y) ? 1 : ((a.y > b.y) ? -1 : 0));
                            });

                            $.each(sortedPoints , function(i, point) {
                                s += point.point.tooltipFormatter('<div style=\"color:{series.color}\">{series.name}</div>: <b>{point.y}</b>" . ($this->type == GraphType::Percentage_Area ? "(' + Highcharts.numberFormat(this.percentage, 1) + '%)" : "") . "<br/>');
                            });

                            return s;
                        }
                    };
                ";
        } else { // Pie chart
            $rawJavascript = "
                ${renderTo}Options.plotOptions =
                {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            color: '#000000',
                            connectorColor: '#000000',
                            formatter: function() {
                                return '<b>' + this.point.name + '</b>: ' + ( Math.round(this.percentage * 10) / 10 ) + ' %';
                            }
                        }
                    }
                };
                ${renderTo}Options.tooltip =
                {
                    \"formatter\": function() {
                        return '<b>' + this.point.name + '</b>: ' + ( Math.round(this.percentage * 100) / 100 ) + ' %';
                    }
                };
            ";

        }

        return $chart->renderChart($renderTo, $classname, $rawJavascript, 'jquery', $this->type);
    }

    /**
     * Check if the graph is read only or not
     * @return bool
     */
    public function isReadOnly() {
        return $this->readonly;
    }

    /**
     * Get the columns present in the graph
     * @return array
     */
    public function getColumns() {
        return $this->columns;
    }

    /**
     * Load the columns for this graph
     * @param $limit_results Only show the most used results, mainly just for displaying in /plugin/
     */
    public function loadColumns($limit_results = false) {
        $this->columns = array();
        $statement = get_slave_db_handle()->prepare('SELECT ID, Name FROM CustomColumn WHERE Plugin = ? AND Graph = ? order by ID ASC');
        $statement->execute(array($this->plugin->getID(), $this->id));

        while (($row = $statement->fetch()) != null) {
            $id = $row['ID'];
            $name = $row['Name'];
            $this->columns[$id] = $name;
        }
    }

    /**
     * Adds a local column (presumed saved)
     *
     * @param $id
     * @param $name
     */
    public function addLocalColumn($id, $name) {
        $this->columns[$id] = $name;
    }

    /**
     * Add a raw series to the graph
     * @param $series HighRollerSeriesData
     */
    public function addSeries($series) {
        $this->series[] = $series;
    }

    /**
     * Set the name of the graph
     * @param $name string
     */
    public function setName($name) {
        $this->name = $name;

        // Set the display name if the internal name is blank
        if ($this->displayName == '') {
            $this->displayName = $name;
        }
    }

    /**
     * Set the display name for the graph
     * @param $displayName string
     */
    public function setDisplayName($displayName) {
        $this->displayName = $displayName;
    }

    /**
     * Set the name of the graph
     * @param $name
     */
    public function setType($type) {
        $this->type = $type;
    }

    /**
     * Set if the graph is active or not
     * @param $active
     */
    public function setActive($active) {
        $this->active = $active;
    }

    /**
     * Get the graph's scale
     * @param $scale string
     */
    public function setScale($scale) {
        $this->scale = $scale;
    }

    /**
     * Get the graph's internal id
     * @return int
     */
    public function getID() {
        return $this->id;
    }

    /**
     * Get the plugin this graph is for
     * @return Plugin
     */
    public function getPlugin() {
        return $this->plugin;
    }

    /**
     * Get the graph's type
     * @return GraphType|int
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Get the graph's name
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Get the graph's display name
     * @return string
     */
    public function getDisplayName() {
        return $this->displayName;
    }

    /**
     * Check if the graph is currently active
     * @return bool
     */
    public function isActive() {
        return $this->active == 1;
    }

    /**
     * Get the graph's scale
     * @return string
     */
    public function getScale() {
        return $this->scale;
    }

    /**
     * @return bool
     */
    public function isHalfwidth() {
        return $this->halfwidth;
    }

    /**
     * Set the halfwidth value for this graph
     * @param $halfwidth
     */
    public function setHalfwidth($halfwidth) {
        $this->halfwidth = $halfwidth;
    }

    /**
     * @return int
     */
    public function getPosition() {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition($position) {
        $this->position = $position;
    }

    /**
     * @return string
     */
    public function getFeedURL() {
        return $this->feedURL;
    }

    /**
     * @param string $feedURL
     */
    public function setFeedURL($feedURL) {
        $this->feedURL = $feedURL;
    }

}