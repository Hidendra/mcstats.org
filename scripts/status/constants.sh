#!/bin/bash

# OS
IS_FREEBSD=0
IS_LINUX=0

if [ "`uname`" = "FreeBSD" ]; then
    IS_FREEBSD=1
    CURL="/usr/local/bin/curl"
else
    IS_LINUX=1
    CURL="/usr/bin/curl"
fi

# binaries
SENDMAIL="/usr/sbin/sendmail"

# If 1, mail will be sent everytime the test-all script is ran
VERBOSE=0

# The frontend url to grab -- only check status code
FRONTEND_URL="http://mcstats.org"

# The base URL used to send data. The plugin name is simply appended.
BASE_URL="http://mcstats.org/report/"

# The guid that is sent to the backend.
# This is unique per status server.
GUID="status-`hostname`"

# The plugin name that is sent to the backend
NAME="MCStatsStatus"

# The current version
# This is sent in the test payload. This can be used to verify that all status servers
# are on the correct and latest version (awesome right!)
VERSION="1.00"

# Report the OS version as the server version
SERVER_VERSION="`uname`"

# The backend revision that is sent.
BACKEND_REVISION="5"