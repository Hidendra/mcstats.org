<?php

$requiredKeys = array('guid', 'version', 'server', 'revision', 'players', 'C~~Test~~Plotter');

foreach ($requiredKeys as $key)
{
    if (!isset($_POST[$key]))
    {
        $handle = fopen('../post-error.log', 'a');
        fwrite($handle, "FAIL\n" . print_r($_POST, true) . "\n");
        fclose($handle);
        exit ('FAIL');
    }
}

echo 'OK';