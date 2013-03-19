#!/bin/bash

. constants.sh
. func.sh

# Tests the front by simply getting the mainpage and return the status code

# send the curl request
RESPONSE=`$CURL --silent --connect-timeout 10 --write-out "%{http_code}" -o /dev/null $FRONTEND_URL`

if [ $RESPONSE -ne 200 ]; then
    # get the content that made it fail (hopefully it fails again?)
    CONTENT=`$CURL --silent --connect-timeout 5 --write-out "%{http_code}" $FRONTEND_URL`

    echo "Error => $RESPONSE"
    send_failure "Frontend failure" "Frontend@\"$FRONTEND_URL\" (`hostname`:\"$GUID\") returned:" "$CONTENT" "" ""
    echo "Email sent"
else
    echo "All good!"
fi
