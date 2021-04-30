#!/bin/bash

# This script runs a bunch of post software deployment
# scripts to finish setting everything up that is not code.

# If running this script from composer, the user should
# pass in the word 'composer' as the only parameter.  This
# will tell the script to check the procfile and only run
# if the procfile will not be running this script.
INPUT=$1

source ./scripts/include.sh

# Check to see if we should run the db_deploy script.  Currently the only
# time you would not want to do this is if you are in a disaster recovery
# situation where you need to push the code before the database is restored.
echo "Checking SKIP_DB_DEPLOY environment variable to see if we should deploy the database or not."
if [[ $(skip_db_deploy) == "Y" ]] ; then
    echo "No, stopping execution of db_deploy."
    exit 0
fi

set_database_vars

# If we are running this script from composer, then we need to
# check the procfile for the release level.  If the Procfile is going
# to run the deploy step, let it.  Do not run this script from composer
# in that case.
set_level_tag
if [[ ${INPUT} == "composer" ]]; then
    if [[ ${LEVEL} != "" ]]; then

        TEST=`cat Procfile.${LEVEL} | grep release: | grep deploy`
        if [[ ${TEST} != "" ]]; then
            echo "Procfile will execute the deploy.sh script later.  Skipping this step while in composer."
            exit
        fi
    fi
fi

#scripts/db_info.sh
scripts/db_install.sh
scripts/db_install_post.sh

# Denote that we completed this task.
log_it "db_deploy.sh is complete."
