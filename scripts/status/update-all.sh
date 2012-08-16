#!/bin/bash

# generates /etc/hosts

HOSTNAME="root@$1"

# transfer the hosts file to each machine
while read MACHINE; do
    echo "Updating: $MACHINE"
    rsync -av --progress ../status root@$MACHINE:
    echo
done < machines