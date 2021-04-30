-- Run background jobs in a dyno.
INSERT INTO "AppOption" ( "Key", "Value") VALUES ( 'DYNO_SUPPORT_ENABLED', 'FALSE' ) ON CONFLICT ("Key") DO UPDATE SET "Value" = 'FALSE';
INSERT INTO "AppOption" ( "Key", "Value") VALUES ( 'ONE_OFF_DYNO_SUPPORT_ENABLED', 'FALSE' ) ON CONFLICT ("Key") DO UPDATE SET "Value" = 'FALSE';
INSERT INTO "AppOption" ( "Key", "Value") VALUES ( 'ONE_OFF_DYNO_SIZE', '' ) ON CONFLICT ("Key") DO UPDATE SET "Value" = '';
INSERT INTO "AppOption" ( "Key", "Value") VALUES ( 'ONE_OFF_DYNO_PHP_MEMORY_LIMIT', '' ) ON CONFLICT ("Key") DO UPDATE SET "Value" = '';

-- Define how we interact with the database
INSERT INTO "AppOption" ( "Key", "Value") VALUES ( 'PSQL_WORK_MEM', '100MB' ) ON CONFLICT ("Key") DO UPDATE SET "Value" = '100MB';
INSERT INTO "AppOption" ( "Key", "Value") VALUES ( 'REST_SECONDS_BETWEEN_QUERIES', '0' ) ON CONFLICT ("Key") DO UPDATE SET "Value" = '0';
INSERT INTO "AppOption" ( "Key", "Value") VALUES ( 'SELECT_INTO_CHUNCK_SIZE', '1000' ) ON CONFLICT ("Key") DO UPDATE SET "Value" = '1000';

-- Turn off developer debug items.
INSERT INTO "AppOption" ( "Key", "Value") VALUES ( 'LOG_DEBUG_MESSAGES', 'FALSE' ) ON CONFLICT ("Key") DO UPDATE SET "Value" = 'FALSE';
INSERT INTO "AppOption" ( "Key", "Value") VALUES ( 'ROLLBACK_ON_CRIT', 'TRUE' ) ON CONFLICT ("Key") DO UPDATE SET "Value" = 'TRUE';

-- Enable/Disable Pusher
INSERT INTO "AppOption" ( "Key", "Value") VALUES ( 'PUSHER_ENABLED', 'TRUE' ) ON CONFLICT ("Key") DO UPDATE SET "Value" = 'TRUE';

-- Enable/Disable VACUUM
INSERT INTO "AppOption" ( "Key", "Value") VALUES ( 'VACUUM_ENABLED', 'TRUE' ) ON CONFLICT ("Key") DO UPDATE SET "Value" = 'TRUE';

-- Set number of years we allow the customer to select.
INSERT INTO "AppOption" ( "Key", "Value") VALUES ( 'GETTING_STARTED_YEARS', '5' ) ON CONFLICT ("Key") DO UPDATE SET "Value" = '5';

-- Create the app option that will allow us to stop processing a backgound job be debug message.
INSERT INTO "AppOption" ( "Key", "Value") VALUES ( 'DEBUG_STOP_STRING', '' ) ON CONFLICT ("Key") DO UPDATE SET "Value" = '';

-- How many keys are we allowed to stock pile?
INSERT INTO "AppOption" ( "Key", "Value") VALUES ( 'KEY_POOL_MAX', '5' ) ON CONFLICT ("Key") DO UPDATE SET "Value" = '5';

-- How many companyparent snapshots can we keep?
INSERT INTO "AppOption" ( "Key", "Value") VALUES ( 'PARENTCOMPANY_SNAPSHOT_MAX', '12' ) ON CONFLICT ("Key") DO UPDATE SET "Value" = '12';

-- What is the max size we are going to allow a session to get in order to download a file?
INSERT INTO "AppOption" ( "Key", "Value") VALUES ( 'DOWNLOAD_FILESIZE_MAX', '791M' ) ON CONFLICT ("Key") DO UPDATE SET "Value" = '791M';
INSERT INTO "AppOption" ( "Key", "Value") VALUES ( 'DOWNLOAD_FILESIZE_PADDING', '100M' ) ON CONFLICT ("Key") DO UPDATE SET "Value" = '100M';