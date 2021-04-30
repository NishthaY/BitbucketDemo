#!/bin/bash

# Deploy runtime files. ( htaccess, config files ) and then install any
# updates into the database that are not yet installed.

# No Parameter  -- Development Configuration
# demo          -- DEMO Configuration
# sandbox       -- SANDBOX Configuration
# prod          -- PROD Configuration


# You may install either development or default.
source ./scripts/include.sh
set_database_vars


## CONFIG FILES
## +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
if [[ ${APP_NAME} == "" ]]
then
    echo  "deploying config files   [failed]"
elif [[ ${APP_NAME} == "a2p-demo" ]]
then
    cd application/config
    for F in *.demo.php; do G=`echo $F | sed -e 's/\.demo\.php/.php/'`; cp -v $F $G; done
    cd ../../
    for F in *.demo.conf; do G=`echo $F | sed -e 's/\.demo\.conf/.conf/'`; cp -v $F $G; done
    for F in *.demo; do G=`echo $F | sed -e 's/\.demo//'`; cp -v $F $G; done
    echo  "[DEMO] deploying config files   [ok]"
elif [[ ${APP_NAME} == "a2p-sandbox" ]]
then
    cd application/config
    for F in *.sandbox.php; do G=`echo $F | sed -e 's/\.sandbox\.php/.php/'`; cp -v $F $G; done
    cd ../../
    for F in *.sandbox.conf; do G=`echo $F | sed -e 's/\.sandbox\.conf/.conf/'`; cp -v $F $G; done
    for F in *.sandbox; do G=`echo $F | sed -e 's/\.sandbox//'`; cp -v $F $G; done
    echo  "[SANDBOX] deploying config files   [ok]"
elif [[ ${APP_NAME} == "a2p-uat" ]]
then
    cd application/config
    for F in *.uat.php; do G=`echo $F | sed -e 's/\.uat\.php/.php/'`; cp -v $F $G; done
    cd ../../
    for F in *.uat.conf; do G=`echo $F | sed -e 's/\.uat\.conf/.conf/'`; cp -v $F $G; done
    for F in *.uat; do G=`echo $F | sed -e 's/\.uat//'`; cp -v $F $G; done
    echo  "[UAT] deploying config files   [ok]"
elif [[ ${APP_NAME} == "a2p-qa" ]]
then
    cd application/config
    for F in *.qa.php; do G=`echo $F | sed -e 's/\.qa\.php/.php/'`; cp -v $F $G; done
    cd ../../
    for F in *.qa.conf; do G=`echo $F | sed -e 's/\.qa\.conf/.conf/'`; cp -v $F $G; done
    for F in *.qa; do G=`echo $F | sed -e 's/\.qa//'`; cp -v $F $G; done
    echo  "[QA] deploying config files   [ok]"
elif [[ ${APP_NAME} == "a2p-prod" ]]
then
    cd application/config
    for F in *.prod.php; do G=`echo $F | sed -e 's/\.prod\.php/.php/'`; cp -v $F $G; done
    cd ../../
    for F in *.prod.conf; do G=`echo $F | sed -e 's/\.prod\.conf/.conf/'`; cp -v $F $G; done
    for F in *.prod; do G=`echo $F | sed -e 's/\.prod//'`; cp -v $F $G; done
    echo  "[PROD] deploying config files   [ok]"
elif [[ ${APP_NAME} == "a2p-prodcopy" ]]
then
    cd application/config
    for F in *.prodcopy.php; do G=`echo $F | sed -e 's/\.prodcopy\.php/.php/'`; cp -v $F $G; done
    cd ../../
    for F in *.prodcopy.conf; do G=`echo $F | sed -e 's/\.prodcopy\.conf/.conf/'`; cp -v $F $G; done
    for F in *.prodcopy; do G=`echo $F | sed -e 's/\.prodcopy//'`; cp -v $F $G; done
    echo  "[PRODCOPY] deploying config files   [ok]"

else
    cd application/config
    for F in *.development.php; do G=`echo $F | sed -e 's/\.development\.php/.php/'`; cp -v $F $G; done
    cd ../../
    pwd
    for F in *.development.conf; do G=`echo $F | sed -e 's/\.development\.conf/.conf/'`; cp -v $F $G; done
    for F in *.development; do G=`echo $F | sed -e 's/\.development//'`; cp -v $F $G; done
    echo  "[DEVLOPMENT] deploying config files   [ok]"
    exit;
fi
