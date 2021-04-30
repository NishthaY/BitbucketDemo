#!/bin/bash

source ./scripts/include.sh
set_database_vars

if [[ $(is_development) == "Y" ]] ; then
    log_it "Executing post-install script for DEV."
    if [[ -e ./database/postinstall/dev.sql ]]; then
        psql -v db=${DB_NAME} -U ${DB_USERNAME} -h ${DB_HOSTNAME} -d ${DB_NAME} -p ${DB_PORT} -a -f database/postinstall/dev.sql
    fi
fi

if [[ $(is_demo) == "Y" ]] ; then
    log_it "Executing post-install script for DEMO."
    if [[ -e ./database/postinstall/demo.sql ]]; then
        psql -v db=${DB_NAME} -U ${DB_USERNAME} -h ${DB_HOSTNAME} -d ${DB_NAME} -p ${DB_PORT} -a -f database/postinstall/demo.sql
    fi
fi

if [[ $(is_uat) == "Y" ]] ; then
    log_it "Executing post-install script for UAT."
    if [[ -e ./database/postinstall/uat.sql ]]; then
        psql -v db=${DB_NAME} -U ${DB_USERNAME} -h ${DB_HOSTNAME} -d ${DB_NAME} -p ${DB_PORT} -a -f database/postinstall/uat.sql
    fi
fi

if [[ $(is_qa) == "Y" ]] ; then
    log_it "Executing post-install script for QA."
    if [[ -e ./database/postinstall/qa.sql ]]; then
        psql -v db=${DB_NAME} -U ${DB_USERNAME} -h ${DB_HOSTNAME} -d ${DB_NAME} -p ${DB_PORT} -a -f database/postinstall/qa.sql
    fi
fi

if [[ $(is_sandbox) == "Y" ]] ; then
    log_it "Executing post-install script for SBOX."
    if [[ -e ./database/postinstall/sbox.sql ]]; then
        psql -v db=${DB_NAME} -U ${DB_USERNAME} -h ${DB_HOSTNAME} -d ${DB_NAME} -p ${DB_PORT} -a -f database/postinstall/sbox.sql
    fi
fi

if [[ $(is_prod) == "Y" ]] ; then
    log_it "Executing post-install script for PROD."
    if [[ -e ./database/postinstall/prod.sql ]]; then
        psql -v db=${DB_NAME} -U ${DB_USERNAME} -h ${DB_HOSTNAME} -d ${DB_NAME} -p ${DB_PORT} -a -f database/postinstall/prod.sql
    fi
fi

if [[ $(is_prodcopy) == "Y" ]] ; then
    log_it "Executing post-install script for PRODCOPY."
    if [[ -e ./database/postinstall/prodcopy.sql ]]; then
        psql -v db=${DB_NAME} -U ${DB_USERNAME} -h ${DB_HOSTNAME} -d ${DB_NAME} -p ${DB_PORT} -a -f database/postinstall/prodcopy.sql
    fi
fi
