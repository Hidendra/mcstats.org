<?php
if (!defined('ROOT')) exit('For science.');

class DataGenerator
{

    /**
     * Generates graph data
     *
     * @static
     * @param $graph Graph
     * @param $columnID int The column ID to generate data for if applicable. Not required for Pie graphs
     * @return array
     */
    public static function generateCustomChartData($graph, $columnID = -1, $hours = 372)
    {
        $_cacheid = 'CustomChart/' . $graph->getID() . '/' . $columnID . '/' . $graph->getType() . '/' . $hours;

        // Check the cache
        if ($data = $graph->getPlugin()->cacheGet($_cacheid))
        {
            return $data;
        }

        $generatedData = array();

        // calculate the minimum
        $baseEpoch = normalizeTime();
        $minimum = strtotime('-' . $hours . ' hours', $baseEpoch);
        $maximum = $baseEpoch;

        if ($graph->getType() == GraphType::Pie)
        {
            // the amounts for each column
            $columnAmounts = array();

            foreach ($graph->getColumns() as $id => $columnName)
            {
                $columnAmounts[$columnName] = $graph->getPlugin()->getTimelineCustomLast($id);
            }

            // Now begin our magic
            asort($columnAmounts);

            // Sum all of the points
            $data_sum = array_sum($columnAmounts);

            // remove low outlier data on large datasets
            if ($data_sum > 1000)
            {
                foreach ($columnAmounts as $columnName => $amount)
                {
                    if ($amount <= 5)
                    {
                        unset ($columnAmounts[$columnName]);
                    }
                }

                // recalculate the data summages
                $data_sum = array_sum($columnAmounts);
            }

            $count = count($columnAmounts);
            if ($count >= MINIMUM_FOR_OTHERS)
            {
                $others_total = 0;

                foreach ($columnAmounts as $columnName => $amount)
                {
                    if ($count <= MINIMUM_FOR_OTHERS)
                    {
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
            foreach ($columnAmounts as $columnName => $dataPoint)
            {
                $percent = round(($dataPoint / $data_sum) * 100, 2);

                // Leave out 0%s !
                if ($percent == 0)
                {
                    continue;
                }

                if (is_numeric($columnName) || is_double($columnName))
                {
                    $columnName = "\0" . $columnName;
                }

                $generatedData[] = array($columnName, $percent);
            }
        } else
        {
            // Get all of the custom data points
            $dataPoints = $graph->getPlugin()->getTimelineCustom($columnID, $minimum, $maximum);

            // Add all of them to the array
            foreach ($dataPoints as $epoch => $dataPoint)
            {
                if ($dataPoint == 0)
                {
                    continue;
                }

                $generatedData[] = array($epoch * 1000, $dataPoint);
            }
        }

        // Cache it
        $graph->getPlugin()->cacheSet($_cacheid, $generatedData);
        return $generatedData;
    }

}