#!/bin/bash


# skip_db_deploy
# Look to the enviornment to decide if we should skip the db deploy
# or not.  If the SKIP_DB_DEPLOY variable is set to YES or TRUE then
# return success.  All other cases return no.
# ----------------------------------------------------------------------
skip_db_deploy()
{
    # Load the personal env file, if we have one.
    if [[ -e ".env" ]]; then
        source .env
    fi

    # SKIP_DB_DEPLOY is defined in the environment.  If it is set to TRUE or YES
    # then we will indicate Y as a response.  In other cases, false.
    if [[ ${SKIP_DB_DEPLOY} == "TRUE" || ${SKIP_DB_DEPLOY} == "YES" ]]; then
        echo "Y"
    else
        echo "N"
    fi
}


# is_development
# This function returns Y or N depending on if it thinks we are running
# on a private sandbox outside of heroku.
# ----------------------------------------------------------------------
is_development()
{
    # Load the personal env file, if we have one.
    if [[ -e ".env" ]]; then
        source .env
    fi

    # HOSTNAME is defined in the environment.  If it's matches the
    # development hostname, return Y.
    if [[ ${HOSTNAME} == "dev.advice2pay.com" ]]; then
        echo "Y"
    else
        echo "N"
    fi

}
is_demo()
{
    # Load the personal env file, if we have one.
    if [[ -e ".env" ]]; then
        source .env
    fi

    # HOSTNAME is defined in the environment.  If it's matches the
    # demo hostname, return Y.
    if [[ ${HOSTNAME} == "demo.advice2pay.com" ]]; then
        echo "Y"
    else
        echo "N"
    fi

}

is_uat()
{
    # Load the personal env file, if we have one.
    if [[ -e ".env" ]]; then
        source .env
    fi

    # HOSTNAME is defined in the environment.  If it's matches the
    # demo hostname, return Y.
    if [[ ${HOSTNAME} == "uat.advice2pay.com" ]]; then
        echo "Y"
    else
        echo "N"
    fi

}

is_qa()
{
    # Load the personal env file, if we have one.
    if [[ -e ".env" ]]; then
        source .env
    fi

    # HOSTNAME is defined in the environment.  If it's matches the
    # demo hostname, return Y.
    if [[ ${HOSTNAME} == "qa.advice2pay.com" ]]; then
        echo "Y"
    else
        echo "N"
    fi

}

is_sandbox()
{
    # Load the personal env file, if we have one.
    if [[ -e ".env" ]]; then
        source .env
    fi

    # HOSTNAME is defined in the environment.  If it's matches the
    # demo hostname, return Y.
    if [[ ${HOSTNAME} == "sandbox.advice2pay.com" ]]; then
        echo "Y"
    else
        echo "N"
    fi

}

is_prod()
{
    # Load the personal env file, if we have one.
    if [[ -e ".env" ]]; then
        source .env
    fi

    # HOSTNAME is defined in the environment.  If it's matches the
    # demo hostname, return Y.
    if [[ ${HOSTNAME} == "dashboard.advice2pay.com" ]]; then
        echo "Y"
    else
        echo "N"
    fi

}

is_prodcopy()
{
    # Load the personal env file, if we have one.
    if [[ -e ".env" ]]; then
        source .env
    fi

    # HOSTNAME is defined in the environment.  If it's matches the
    # demo hostname, return Y.
    if [[ ${HOSTNAME} == "prodcopy.advice2pay.com" ]]; then
        echo "Y"
    else
        echo "N"
    fi

}


# set_level_tag
# This function will set the variable LEVEL to match the configuration
# tag for the corresponding level.  This can then be used to access
# the correct config file for additional information.
# ----------------------------------------------------------------------
set_level_tag()
{
    if [[ $(is_development) == "Y" ]] ; then
        LEVEL=development
    fi

    if [[ $(is_uat) == "Y" ]] ; then
        LEVEL=uat
    fi

    if [[ $(is_qa) == "Y" ]] ; then
        LEVEL=qa
    fi

    if [[ $(is_sandbox) == "Y" ]] ; then
        LEVEL=sandbox
    fi

    if [[ $(is_demo) == "Y" ]] ; then
        LEVEL=demo
    fi

    if [[ $(is_prod) == "Y" ]] ; then
        LEVEL=prod
    fi

    if [[ $(is_prodcopy) == "Y" ]] ; then
        LEVEL=prodcopy
    fi
}

# set_database_vars
# This function will use the DATABASE_URL environment variable and
# parse all of the data points into individual variables.  Once DB_HOSTNAME
# it will create a .pgpass file with the variables so we can run scripts
# against the postgres database.
# ----------------------------------------------------------------------
set_database_vars()
{
    # In development and want to control your environment variables?
    # Just set your variables in .env off the root of the application and
    # this script will use those.
    if [[ -e ".env" ]]; then
        source .env
    fi


    # For Testing.
    #A2P_DEV_DATABASE_URL=postgres://username-dev:p@ssword-dev@hostname-dev:port/database-dev
    #DATABASE_URL=postgres://username:p@ssword@hostname:port/database
    #echo "string     [${DATABASE_URL}]"
    #echo "string     [${A2P_DEV_DATABASE_URL}]"


    # DATABASE_URL vs TAG_DATABASE_URL
    #
    # Swap out a DATABASE_URL with TAG_DATABASE_URL where TAG is similar to
    # the APP_NAME.  For example if we have A2P_DEV_DATABASE_URL and
    # our APP_NAME is 'a2p-dev', we will set the DATABASE_URL to the value
    # of A2P_DEV_DATABASE_URL.
    # --------------------------------------------------------------------
    TAG=${APP_NAME^^}                       # Uppercase APP_NAME
    TAG=`echo ${TAG} | sed 's/-/_/g'`       # Replace - with +_
    TAG=${TAG}_DATABASE_URL                 # Create the variable name we are looking for.
    if [[ ${!TAG} != "" ]]; then
        DATABASE_URL=${!TAG}                # The ! operator says get the ENV value for the variable name like this string.
    fi


    # fRightBack( DATABASE_URL, @)
    FRIGHT_BACK=${DATABASE_URL##*@}
    #echo "fRightBack [${FRIGHT_BACK}]"

    #fLeftBack( DATABASE_URL, @)
    FLEFT_BACK=`echo ${DATABASE_URL/@${FRIGHT_BACK}/}`
    #echo "fLeftBack  [${FLEFT_BACK}]"

    # Create our "parts"
    DB_USERNAME=`echo ${FLEFT_BACK} | cut -d/ -f3 | cut -d: -f1`
    DB_HOSTNAME=`echo ${FRIGHT_BACK} | cut -d: -f1`
    DB_PORT=`echo ${FRIGHT_BACK} | cut -d: -f2 | cut -d/ -f1`
    DB_NAME=`echo ${FRIGHT_BACK} | cut -d: -f2 | cut -d/ -f2`
    DB_PASSWORD=`echo ${FLEFT_BACK} | cut  -d: -f3`

    # capture the USER password from the existing pgpass file so the
    # end user only needs to enter it the first time.
    if [[ $(is_development) == "Y" ]]; then
        # If we are in development, pull out the running users password so
        # we can restore it without asking them for it.
        if [[ -e ~/.pgpass ]]; then
          PG_PASS=`cat ~/.pgpass | grep ${USER} | head -1 | cut -d : -f5`
        fi
    fi

    # create a .pgpass file
    echo "${DB_HOSTNAME}:${DB_PORT}:${DB_NAME}:${DB_USERNAME}:${DB_PASSWORD}" > ${HOME}/.pgpass
    if [[ $(is_development) == "Y" ]]; then

        # Only ask the user for the password if we don't already know it.
        if [[ "${PG_PASS}" == "" ]]; then
          read -p "What is the database password for ${USER}: " PG_PASS
        fi

        # Heroku is a one stop shop for the database user.  On nitrous I
        # need to other users to manage the database.  Add those here.
        echo "localhost:${DB_PORT}:postgres:${USER}:${PG_PASS}" >> ${HOME}/.pgpass           # USED TO DESTROY THE DATABASE
        echo "localhost:${DB_PORT}:advice2pay:${USER}:${PG_PASS}" >> ${HOME}/.pgpass         # USED TO CREATE THE DATABASE

    fi
    chmod 600 ${HOME}/.pgpass

    # Debug
    #echo DB_USERNAME[${DB_USERNAME}]
    #echo DB_HOSTNAME[${DB_HOSTNAME}]
    #echo DB_PORT[${DB_PORT}]
    #echo DB_NAME[${DB_NAME}]
    #echo DB_PASSWORD[${DB_PASSWORD}]

}

does_database_exist()
{
    if [[ $(is_development) == "Y" ]]; then

        set_database_vars
        cmd="psql -U ${USER} -h localhost -d postgres -p ${DB_PORT} -t -c \"select count(*) from pg_database where datname='${DB_NAME}'\" "
        EXISTS=`eval $cmd`
        EXISTS=$(( ${EXISTS} + 0 ))
        if [[ ${EXISTS} -eq 0 ]] ; then
            echo "N"
        else
            echo "Y"
        fi

    else
        echo "Y"
    fi


}

does_table_exist()
{
    set_database_vars
    TABLE_NAME=$1
    cmd="psql -U ${DB_USERNAME} -h ${DB_HOSTNAME} -d ${DB_NAME} -p ${DB_PORT} -t -c \"SELECT EXISTS (SELECT 1 FROM information_schema.tables WHERE  table_schema = 'public' AND    table_name = '${TABLE_NAME}')\" "
    EXISTS=`eval $cmd`
    EXISTS="$(echo -e "${EXISTS}" | tr -d '[[:space:]]')"
    if [[ ${EXISTS} == "t" ]]; then
        echo "Y"
    else
        echo "N"
    fi

}

log_it()
{
    MESSAGE=$1
    TAG=$2

    if [[ ${TAG} == "" ]]; then
        TAG=release
    fi

    echo ${MESSAGE}

    if [[ $(is_development) == "N" ]] ; then
        # Denote that we completed this task.
        SQL="insert into \\\"Log\\\" (\\\"ShortDesc\\\", \\\"LongDesc\\\", \\\"Session\\\") values ( '${TAG}', '${MESSAGE}', ''); "
        CMD="psql -v db=${DB_NAME} -U ${DB_USERNAME} -h ${DB_HOSTNAME} -d ${DB_NAME} -p ${DB_PORT} -t -c \"${SQL}\" 1>/dev/null 2>/dev/null"
        `eval ${CMD}`
    fi

}