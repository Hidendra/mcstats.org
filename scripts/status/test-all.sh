#!/bin/bash

. constants.sh

# test everything

# backend
echo "Testing backend"
./test-backend.sh

# frontend
echo
echo "Testing frontend"
./test-frontend.sh

if [ $VERBOSE -eq 1 ]; then
    (   echo "Subject: `hostname` ran" ;
        echo "From: Charlie Root <root@`hostname`>" ;
        echo "To: hidendra@mcstats.org" ;
        echo "" ;
    ) | $SENDMAIL -t
fi