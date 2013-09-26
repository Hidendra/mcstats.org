<?php

define('ROOT', '../private_html/');
define('MAX_COLUMNS', 50); // soft limit of max amount of columns to loop through per plugin
define('MAX_CHILDREN', 30); // the maximum amount of children that can be started

require_once ROOT . 'config.php';
require_once ROOT . 'includes/database.php';
require_once ROOT . 'includes/func.php';

$queries = <<<END
UPDATE Graph SET Type = 1, Active = 1, Readonly = 1, Halfwidth = 0, Position = 1 WHERE Name = 'Global Statistics';
UPDATE Graph SET Type = 3, Active = 1, Readonly = 1, Halfwidth = 0, Position = 9000 WHERE Name = 'Server Locations';
UPDATE Graph SET Type = 3, Active = 1, Readonly = 1, Halfwidth = 1, Position = 9001 WHERE Name = 'Game Version';
UPDATE Graph SET Type = 3, Active = 1, Readonly = 1, Halfwidth = 1, Position = 9002 WHERE Name = 'Server Software';
UPDATE Graph SET Type = 1, Active = 1, Readonly = 1, Halfwidth = 0, Position = 9003 WHERE Name = 'Version Trends';
UPDATE Graph SET Type = 4, Active = 1, Readonly = 1, Halfwidth = 0, Position = 9004 WHERE Name = 'Version Demographics';

UPDATE Graph SET Type = 6, Active = 1, Readonly = 1, Halfwidth = 1, Position = 8500 WHERE Name = 'Operating System';
UPDATE Graph SET Type = 6, Active = 1, Readonly = 1, Halfwidth = 1, Position = 8300 WHERE Name = 'Java Version';
UPDATE Graph SET Type = 3, Active = 1, Readonly = 1, Halfwidth = 1, Position = 8600 WHERE Name = 'Auth Mode';
UPDATE Graph SET Type = 3, Active = 1, Readonly = 1, Halfwidth = 1, Position = 8601, DisplayName = 'MCStats Revision (# plugins)' WHERE Name = 'MCStats Revision';
UPDATE Graph SET Type = 3, Active = 1, Readonly = 1, Halfwidth = 1, Position = 8700 WHERE Name = 'System Arch';
UPDATE Graph SET Type = 3, Active = 1, Readonly = 1, Halfwidth = 1, Position = 8800 WHERE Name = 'System Cores';


UPDATE Graph SET Type = 1, Active = 1, Readonly = 1, Halfwidth = 1, Position = 8600 WHERE Name = 'Rank';
UPDATE Graph SET Type = 3, Active = 1, Readonly = 1, Halfwidth = 1, Position = 8601 WHERE Name = 'Auth Mode' AND Plugin >= 1;
UPDATE Graph SET Type = 3, Active = 1, Readonly = 1, Halfwidth = 0, Position = 8602 WHERE Name = 'MCStats Revision' AND Plugin >= 1;
END;

foreach (explode(';', $queries) as $query) {
    if (trim($query) == '') {
        continue;
    }

    $statement = $master_db_handle->exec($query);
}

// update latest epoch
$m_statistic->update(array(
    '_id' => 1
), array(
    '$set' => array(
        'max.epoch' => intval(normalizeTime())
    )
), array(
    'upsert' => true
));