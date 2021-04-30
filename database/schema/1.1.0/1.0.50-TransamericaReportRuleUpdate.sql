\set db advice2pay

-- Add an "Enabled" field so we can disable detail records and remove them from the report.
ALTER TABLE "ReportTransamericaEligibilityDetails" ADD "IssueCode" TEXT NULL;

-- Let Pusher know what to say when we try and create a warnings report.
INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'generatereports', 'WARNINGS_REPORT', 'Generating potential issues report.', 'Background task status notification message.' );

-- Add a new report type "Process Report". This is the warnings/issues we show for monthly run.
insert into "ReportType" ( "Id", "Name", "Display" ) values ( 8, 'issues', 'Process Report' )