<?php
if (!defined('ROOT')) {
    exit('For science.');
}

class DataGenerator {

    /**
     * Generates graph data
     *
     * @static
     * @param $graph Graph
     * @param $columnID int The column ID to generate data for if applicable. Not required for Pie graphs
     * @return array
     */
    public static function generateCustomChartData($graph, $columnID = -1, $hours = 372) {
        $_cacheid = 'CustomChart/' . $graph->getID() . '/' . $columnID . '/' . $graph->getType() . '/' . $hours;

        // Check the cache
        // if ($data = $graph->getPlugin()->cacheGet($_cacheid)) {
        //     return json_decode($data);
        //}

        $generatedData = array();

        // calculate the minimum
        $baseEpoch = getLastGraphEpoch();
        $minimum = strtotime('-' . $hours . ' hours', $baseEpoch);
        $maximum = $baseEpoch;

        if ($graph->getType() == GraphType::Pie) {
            // the amounts for each column
            $columnAmounts = array();

            foreach ($graph->getColumns() as $id => $columnName) {
                $columnAmounts[$columnName] = $graph->getPlugin()->getTimelineCustomLast($id, $graph);
            }

            // Now begin our magic
            asort($columnAmounts);

            // Sum all of the points
            $data_sum = array_sum($columnAmounts);

            // remove low outlier data on large datasets
            if ($data_sum > 1000) {
                foreach ($columnAmounts as $columnName => $amount) {
                    if ($amount <= 5) {
                        unset ($columnAmounts[$columnName]);
                    }
                }

                // recalculate the data summages
                $data_sum = array_sum($columnAmounts);
            }

            $count = count($columnAmounts);
            if ($count >= MINIMUM_FOR_OTHERS) {
                $others_total = 0;

                foreach ($columnAmounts as $columnName => $amount) {
                    if ($count <= MINIMUM_FOR_OTHERS) {
                        break;
                    }

                    $count--;
                    $others_total += $amount;
                    unset($columnAmounts[$columnName]);
                }

                // Set the 'Others' stat
                $columnAmounts['Others'] = $others_total;

                // Sort again
                arsort($columnAmounts);
            }

            // Now convert it to %
            foreach ($columnAmounts as $columnName => $dataPoint) {
                $percent = round(($dataPoint / $data_sum) * 100, 2);

                // Leave out 0%s !
                if ($percent == 0) {
                    continue;
                }

                if (is_numeric($columnName) || is_double($columnName)) {
                    // $columnName = "\0" . $columnName;
                }

                $generatedData[] = array($columnName . ' (' . $dataPoint . ')', $percent);
            }

            if (count($generatedData) == 0) {
                $generatedData[] = array('NO DATA', 100);
            }
        } else {
            if ($graph->getType() == GraphType::Donut) {
                // the amounts for each column
                $columnAmounts = array();

                foreach ($graph->getColumns() as $id => $columnName) {
                    $columnAmounts[$columnName] = $graph->getPlugin()->getTimelineCustomLast($id, $graph);
                }

                // Now begin our magic
                asort($columnAmounts);

                // Sum all of the points
                $data_sum = array_sum($columnAmounts);

                // remove low outlier data on large datasets
                if ($data_sum > 1000) {
                    foreach ($columnAmounts as $columnName => $amount) {
                        $percent = round(($amount / $data_sum) * 100, 2);

                        if ($percent <= 0.25) {
                            $expl = explode('~=~', $columnName);
                            unset ($columnAmounts[$columnName]);

                            $otherName = $expl[0] . '~=~Others';
                            if (!isset($columnAmounts[$otherName])) {
                                $columnAmounts[$otherName] = 0;
                            } else {
                                $columnAmounts[$otherName] += round($percent * $data_sum / 100);
                            }
                        }
                    }

                    // recalculate the data summages
                    $data_sum = array_sum($columnAmounts);
                    asort($columnAmounts);
                }

                // Now convert it to %
                $amountsInner = array();
                foreach ($columnAmounts as $columnName => $dataPoint) {
                    $percent = round(($dataPoint / $data_sum) * 100, 2);

                    // Leave out 0%s !
                    if ($percent == 0) {
                        continue;
                    }

                    if (is_numeric($columnName) || is_double($columnName)) {
                        // $columnName = "\0" . $columnName;
                    }

                    // explode the string on the delimiter
                    $expl = explode('~=~', $columnName);
                    $innerName = $expl[0];
                    $outerName = $expl[1];

                    $amountsInner[$innerName] += $dataPoint;

                    $generatedData[$innerName][] = array("name" => $outerName . ' (' . $dataPoint . ')', "y" => $percent);
                }

                foreach ($amountsInner as $innerName => $amount) {
                    $generatedData[$innerName . '<br/>(' . $amount . ')'] = $generatedData[$innerName];
                    unset($generatedData[$innerName]);
                }
            } else {
                // Get all of the custom data points
                $dataPoints = $graph->getPlugin()->getTimelineCustom($columnID, $minimum, $graph);

                // Add all of them to the array
                foreach ($dataPoints as $epoch => $dataPoint) {
                    if ($dataPoint == 0) {
                        continue;
                    }

                    $generatedData[] = array($epoch * 1000, $dataPoint);
                }
            }
        }

        // Cache it
        // $graph->getPlugin()->cacheSet($_cacheid, json_encode($generatedData));
        return $generatedData;
    }

    /**
     * Generate data required for a geochart for a plugin
     * @param $plugin Plugin
     * @return the data
     */
    public static function generateGeoChartData($plugin) {
        $data = array();
        $locations = $plugin->getGraphByName('Server Locations');

        $data[] = array('Country', 'Servers');

        foreach ($locations->getColumns() as $id => $country) {
            $count = $plugin->getTimelineCustomLast($id, $locations);

            if ($count > 0) {
                $data[] = array($country, $count);
            }
        }

        return $data;
    }

}