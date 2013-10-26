#!/usr/local/bin/bash

# executed every 30 minutes

cd /data/www/mcstats.org/cron/

GENERATOR_FILE="../generator.txt"

function run_script {
    echo "Running: $1"
    /usr/local/bin/php -q "$1"
}

run_script clear-cache.php

run_script finish-graph-generation.php
rm "$GENERATOR_FILE"