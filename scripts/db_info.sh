#!/bin/bash

# This is a test script that will let you know if we are in development or
# not and what the database connection variables are.

source ./scripts/include.sh

if [[ $(is_development) == "Y" ]] ; then
    echo "Your are running on DEV."
fi
if [[ $(is_demo) == "Y" ]] ; then
    echo "You are running on DEMO."
fi
if [[ $(is_uat) == "Y" ]] ; then
    echo "You are running on UAT."
fi
if [[ $(is_qa) == "Y" ]] ; then
    echo "You are running on QA."
fi
if [[ $(is_sandbox) == "Y" ]] ; then
    echo "You are running on SANDBOX."
fi
if [[ $(is_prod) == "Y" ]] ; then
    echo "You are running on PROD."
fi
if [[ $(is_prodcopy) == "Y" ]] ; then
    echo "You are running on PRODCOPY."
fi

set_database_vars
if [[ ${DB_PASSWORD} != "" ]]; then
    echo DB_USERNAME[${DB_USERNAME}]
    echo DB_HOSTNAME[${DB_HOSTNAME}]
    echo DB_PORT[${DB_PORT}]
    echo DB_NAME[${DB_NAME}]
    echo DB_PASSWORD[${DB_PASSWORD}]
else
    echo "WARNING: Database variables not set."
fi
