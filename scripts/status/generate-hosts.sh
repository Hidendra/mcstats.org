#!/bin/bash

# generates /etc/hosts

HOSTNAME="root@$1"
TEMP_HOSTS=".tmp_hosts-$RANDOM"

# add localhost
echo 127.0.0.1 localhost localhost.localdomain >> "$TEMP_HOSTS"
echo ::1 localhost >> "$TEMP_HOSTS"

while read MACHINE; do
    # generate the short named version (e.g orl01.status.mcstats.org becomes just orl01)
    IFS='.' read -ra SPLIT <<< "$MACHINE"
    MACHINE_SHORTHAND=${SPLIT[0]}

    echo "`dig +short $MACHINE` $MACHINE $MACHINE_SHORTHAND" >> "$TEMP_HOSTS"
done < machines

# transfer the hosts file to each machine
while read MACHINE; do
    echo "Uploading hosts file to: $MACHINE"
    rsync -av --progress "$TEMP_HOSTS" $MACHINE:/etc/hosts
    echo
done < machines

# cleanup
rm "$TEMP_HOSTS"