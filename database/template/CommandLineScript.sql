#!/bin/bash

source ./scripts/include.sh
set_database_vars

psql -q db=${DB_NAME} -U ${DB_USERNAME} -h ${DB_HOSTNAME} -d ${DB_NAME} -p ${DB_PORT} -c "{SQL_STATEMENT}" >&/dev/null

if [ $? != 0 ]; then
     exit 1
fi
