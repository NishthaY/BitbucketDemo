#!/bin/bash

# This script will redirect any STDIN text to psql.  This is used to import
# predefined sql files.

source ./scripts/include.sh

set_database_vars

# Does the databse exist?  If not, create it.
if [[ $(is_development) == "Y" && $(does_database_exist) != "Y" ]] ; then
    # Make sure this message for development starts with the word 'abort'.  The application will
    # see this and notify the developer of the problem.
    echo "Abort.  Database does not exist or you don't have permission to access it."
else
    input="-"
    cat ${input} | psql -v db=${DB_NAME} -U ${DB_USERNAME} -h ${DB_HOSTNAME} -d ${DB_NAME} -p ${DB_PORT} -a
fi
