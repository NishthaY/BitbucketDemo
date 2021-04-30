#!/bin/bash

# This script assumes the database referenced by DATABASE_URL environment
# variable has been created.  It will then apply schema changes, if it has not
# yet done so before based on the files found in the schema folder.

source ./scripts/include.sh

set_database_vars
log_it "Installing ${DB_NAME} changes."

# Does the databse exist?  If not, create it.
if [[ $(is_development) == "Y" && $(does_database_exist) != "Y" ]] ; then
    log_it "Abort.  Database does not exist."
else
    # Look at our ordered list of install files and check to see if they are INSTALLED
    # in the database or not.  If not, execute the sql file against the database and then
    # record it has been installed in the SchemaChangeLog table
    FILES=`find ./database/schema | grep .sql | sort -V`
    for FILE in ${FILES}
    do

    	FILENAME=`echo ${FILE} | cut -d\/ -f5`
    	FOLDER=`echo ${FILE} | cut -d\/ -f4`

        MAJOR=`echo ${FILENAME} | cut -d. -f1`
        MINOR=`echo ${FILENAME} | cut -d. -f2`
        POINT=`echo ${FILENAME} | cut -d. -f3 | cut -d- -f1`


        if [[ ${MAJOR} -eq 0 && ${MINOR} -eq 0 && ${POINT} -eq 0 ]]; then
            # 0.0.0 - Do not load this file.  Run the db_create.sh script if you
            # need to create a database.
            log_it "Skipping 0.0.0-CreateDatabase.sql file."
            continue
        fi

        if [[ ${MAJOR} -eq 0 && ${MINOR} -eq 0 && ${POINT} -eq 1 ]]; then
            # 0.0.1 - Only run this file if the SchemaChangeLog is missing.
            # need to create a database.
            log_it "Checking to ee if the SchemaChangeLog table exits or not."
            if [[ $(does_table_exist SchemaChangeLog) == "N" ]]; then
                log_it "Creating the SchemaChangeLog table."
                psql -v db=${DB_NAME} -U ${DB_USERNAME} -h ${DB_HOSTNAME} -d ${DB_NAME} -p ${DB_PORT} -a -f database/schema/${FOLDER}/0.0.1-SchemaChangeLog.sql
            fi
            continue
        fi


        log_it "Processing ${FILENAME} in ${FOLDER}"
        #log_it "  MAJOR[${MAJOR}]"
        #log_it "  MINOR[${MINOR}]"
        #log_it "  POINT[${POINT}]"

        # Does a record exist for this file already?
        cmd="psql -U ${DB_USERNAME} -h ${DB_HOSTNAME} -d ${DB_NAME} -p ${DB_PORT} -t -c \"select count(*) from \\\"SchemaChangeLog\\\"  where \\\"ScriptName\\\"='${FILENAME}'\" "
        EXISTS=`eval $cmd`
        EXISTS=$(( ${EXISTS} + 0 ))
        log_it "EXISTS[${EXISTS}]"

        # If a record exists, is it installed?
        INSTALLED=0
        if [[ ${EXISTS} -eq 1 ]]; then
            cmd="psql -U ${DB_USERNAME} -h ${DB_HOSTNAME} -d ${DB_NAME} -p ${DB_PORT} -t -c \"select count(*) from \\\"SchemaChangeLog\\\"  where \\\"ScriptName\\\"='${FILENAME}' and \\\"DateApplied\\\" is not null \" "
            INSTALLED=`eval $cmd`
            INSTALLED=$(( ${INSTALLED} + 0 ))
        fi
        log_it "INSTALLED[${INSTALLED}]"

        # Install the SQL file if not installed.
        if [[ ${INSTALLED} -eq 0 ]]; then

            psql -v db=${DB_NAME} -U ${DB_USERNAME} -h ${DB_HOSTNAME} -d ${DB_NAME} -p ${DB_PORT} -a -f database/schema/${FOLDER}/${FILENAME}

            # Save to the SchemaChangeLog that the install script executed.
            SQL="insert into \\\"SchemaChangeLog\\\" ( \\\"MajorReleaseNumber\\\", \\\"MinorReleaseNumber\\\", \\\"PointReleaseNumber\\\", \\\"ScriptName\\\", \\\"DateApplied\\\" ) values ( ${MAJOR}, ${MINOR}, ${POINT}, '${FILENAME}', CURRENT_TIMESTAMP ); "
            CMD="psql -v db=${DB_NAME} -U ${DB_USERNAME} -h ${DB_HOSTNAME} -d ${DB_NAME} -p ${DB_PORT} -t -c \"${SQL}\" 1>/dev/null 2>/dev/null"
            `eval ${CMD}`

        fi



    done
fi