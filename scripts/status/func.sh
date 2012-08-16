#!/bin/bash

# send an email
# Parameters:
# 1 - email subject
# 2 - Line 1
# 3 - Line 2 (2 lines below 1)
# 4 - Line 3 (3 lines below 2)
# 5 - Line 4 (4 lines below 3)
send_failure()
{
    (   echo "Subject: $1" ;
        echo "From: Charlie Root <root@`hostname`>" ;
        echo "To: hidendra@mcstats.org" ;
        echo "$2" ;
        echo "" ;
        echo "$3" ;
        echo "" ;
        echo "$4" ;
        echo "" ;
        echo "$5"
        echo "" ;
    ) | $SENDMAIL -t
}