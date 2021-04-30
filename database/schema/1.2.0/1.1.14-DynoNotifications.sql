\set db advice2pay

-- Let Pusher know what to say as a dyno is provisioned.
INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'queuedirector', 'DYNO_INITIALIZING', 'Creating a secure private instance.', 'Background task status notification message.' );
