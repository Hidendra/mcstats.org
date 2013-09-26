#!/usr/local/bin/bash

# executed every 30 minutes

cd /data/www/mcstats.org/cron/

GENERATOR_FILE="../generator.txt"

function run_script {
    echo "Running: $1"
    /usr/local/bin/php -q "$1"
}

#echo 8 > "$GENERATOR_FILE"
#run_script buffered-generator.php
#echo 99 > "$GENERATOR_FILE"

run_script generators/global-statistics.php
echo 12 > "$GENERATOR_FILE"
run_script generators/custom.php
echo 28 > "$GENERATOR_FILE"
run_script generators/version-trends.php
echo 31 > "$GENERATOR_FILE"
run_script generators/version-demographics.php
echo 36 > "$GENERATOR_FILE"
run_script generators/server-software.php
echo 45 > "$GENERATOR_FILE"
run_script generators/revision.php
echo 40 > "$GENERATOR_FILE"
run_script generators/game-version.php
echo 55 > "$GENERATOR_FILE"
run_script generators/operating-system.php
echo 63 > "$GENERATOR_FILE"
run_script generators/java-version.php
echo 70 > "$GENERATOR_FILE"
run_script generators/auth-mode.php
echo 74 > "$GENERATOR_FILE"
run_script generators/system-arch.php
echo 80 > "$GENERATOR_FILE"
run_script generators/system-cores.php
echo 88 > "$GENERATOR_FILE"
run_script generators/server-locations.php
echo 99 > "$GENERATOR_FILE"
run_script generators/rank.php

run_script server-counts.php
run_script clear-cache.php

# finish !
run_script finish-graph-generation.php
rm "$GENERATOR_FILE"