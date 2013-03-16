#!/bin/bash

. constants.sh
. func.sh

# Tests the backend by sending a post request to it

RAW_DATA="guid=$GUID&server=$SERVER_VERSION&version=$VERSION&revision=$BACKEND_REVISION&players=0&ping=1"

# output some possibly useful data
echo "GUID: $GUID"
echo "ServerVersion: $SERVER_VERSION"

# send the curl request
RESPONSE=`$CURL --silent --connect-timeout 10 --write-out "http_code=%{http_code} time_total=%{time_total}" --data "$RAW_DATA" $BASE_URL$NAME`

if [[ $RESPONSE != OK* ]]; then
    echo "Error => $RESPONSE"
    send_failure "Backend failure" "Backend@\"$BASE_URL$NAME\" (`hostname`:\"$GUID\") returned:" "$RESPONSE" "I sent:" "$RAW_DATA"
    echo "Email sent"
else
    echo "All good!"
fi