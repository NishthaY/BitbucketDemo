#!/bin/bash

# This script will create a development potgres database for the project.
# The database connection string is defined in the environment variable
# DATABASE_URL.  This script will only run on nitrous, not heroku.

source ./scripts/include.sh

if [[ $(is_development) == "Y" ]]; then

    set_database_vars   # create .pgpass file if needed.
    DB_ADMIN_USERNAME=${USER}

    # Does the databse exist?  If not, create it.
    if [[ $(does_database_exist) != "Y" ]] ; then
        echo "Development database does not yet exist.  Creating now..."
        echo "db variable is [${DB_NAME}]"
        echo "username variable is [${DB_USERNAME}]"
        echo "Running this as user [${DB_ADMIN_USERNAME}]"
        psql -v db=${DB_NAME} -v username=${DB_USERNAME} -U ${DB_ADMIN_USERNAME} -h ${DB_HOSTNAME} -d postgres -p ${DB_PORT} -a -f database/schema/0.0.0/0.0.0-CreateDatabase.sql
    else
        echo "Development database already exits."
    fi

    echo "db_create done."
fi
