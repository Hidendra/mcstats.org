#!/bin/bash

if [ $# -ne 1 ]; then
    echo "Usage: $0 <realm>"
    exit
fi

REALM="$1"

# rsync command
RSYNC="rsync -avzq --progress"

# nginx load balancer
REMOTE_HOST="root@mcstats.org"

if [ -d "www" ]; then
	cd www/
else
	cd ../www/
fi

if [ "$REALM" == "live" ]; then
    REMOTE_HOST="root@mcstats.org"
    REMOTE_LOCATION="/data/www/"
elif [ "$REALM" == "dev" ]; then
	REMOTE_HOST="root@192.168.1.50"
	REMOTE_LOCATION="/data/www/"
else
    REMOTE_HOST="root@10.10.1.50"
    REMOTE_LOCATION="/data/www/test.mcstats.org/"
    $RSYNC -e "ssh root@zero.mcstats.org ssh" --exclude 'config.php' static.mcstats.org $REMOTE_HOST:"/data/www/"
    cd mcstats.org
fi

echo -e "Realm: \033[0;32m$REALM\033[00m"

# First deploy to the load balancer
echo "Deploying to nginx load balancer"

if [ "$REALM" == "live" ] || [ "$REALM" == "dev" ]; then
    $RSYNC  --exclude 'config.php' ./ $REMOTE_HOST:"$REMOTE_LOCATION"
else
    $RSYNC -e "ssh root@zero.mcstats.org ssh" --exclude 'config.php' ./ $REMOTE_HOST:"$REMOTE_LOCATION"
fi

echo -e " \033[0;32m=>\033[00m Done"