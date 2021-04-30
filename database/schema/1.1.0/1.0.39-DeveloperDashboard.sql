\set db advice2pay

-- Add a new background task that will refresh the A2P support dashboard.
insert into "BackgroundTask" ( "Name", "RefreshMinutes", "RefreshEnabled", "DebugUser", "InfoUser" ) values ( 'admin_dashboard_task', 1, true, 'brian@advice2pay.com', 'brian@advice2pay.com');


-- Dump the verbiage table and add a group column.
-- Add in text we can show on a background task status update event.
DROP TABLE "Verbiage";
CREATE TABLE "Verbiage"
(
  "Group" text NOT NULL,
  "Key" text NOT NULL,
  "Verbiage" text NOT NULL,
  "Notes" text NULL
)
WITH (
OIDS=FALSE
);
ALTER TABLE "Verbiage" OWNER TO :db;

INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'parsecsvupload', 'EMPTY_STRING', '', 'Background task status notification message.' );
INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'parsecsvupload', 'STARTING', 'Importing your data.', 'Background task status notification message.' );
INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'parsecsvupload', 'ORGANIZING', 'Organizing your data.', 'Background task status notification message.' );
INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'parsecsvupload', 'SECURING', 'Securing your data.', 'Background task status notification message.' );
INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'parsecsvupload', 'SCANNING', 'Scanning your data.', 'Background task status notification message.' );
INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'validatecsvupload', 'EMPTY_STRING', '', 'Background task status notification message.' );
INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'validatecsvupload', 'STARTING', 'Validating your data.', 'Background task status notification message.' );
INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'validatecsvupload', 'VALIDATING_COLUMN', 'Validating {COLUMN_NAME}.', 'Background task status notification message.' );
INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'validatecsvupload', 'CHECKING', 'Checking validation log.', 'Background task status notification message.' );
INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'validatecsvupload', 'PREPARING', 'Preparing validation report.', 'Background task status notification message.' );
INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'validatecsvupload', 'SCANNING', 'Scanning plan data.', 'Background task status notification message.' );
INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'generateimportfiles', 'EMPTY_STRING', '', 'Background task status notification message.' );
INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'generateimportfiles', 'STARTING', 'Saving validated data.', 'Background task status notification message.' );
INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'loadimportfiles', 'EMPTY_STRING', '', 'Background task status notification message.' );
INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'loadimportfiles', 'STARTING', 'Commiting validated data.', 'Background task status notification message.' );
INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'generatereports', 'EMPTY_STRING', '', 'Background task status notification message.' );
INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'generatereports', 'STARTING', 'Generating reports.', 'Background task status notification message.' );
INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'generatereports', 'PLAN_FEES', 'Generating plan fees.', 'Background task status notification message.' );
INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'generatereports', 'AGE_DATA', 'Calculating ages.', 'Background task status notification message.' );
INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'generatereports', 'WASHING', 'Washing the data.', 'Background task status notification message.' );
INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'generatereports', 'DUPLICATE_LIVES', 'Looking for duplicate lives.', 'Background task status notification message.' );
INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'generatereports', 'RETRO_RULES', 'Applying retro rules.', 'Background task status notification message.' );
INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'generatereports', 'RELATIONSHIPS', 'Evaluating relationships between lives.', 'Background task status notification message.' );
INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'generatereports', 'AUTOMATIC_ADJUSTMENTS', 'Applying automatic adjustments.', 'Background task status notification message.' );
INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'generatereports', 'SUMMARY_DATA', 'Summarizing data.', 'Background task status notification message.' );
INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'generatereports', 'ORIGINAL_EFFECTIVE_DATES', 'Locking down original effective dates.', 'Background task status notification message.' );
INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'generatereports', 'BILLING_REPORTS', 'Generating billing reports.', 'Background task status notification message.' );
INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'generatereports', 'TRANSAMERICA_ELIGIBILITY_REPORT', 'Generating Transamerica eligibility import file.', 'Background task status notification message.' );
INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'generatereports', 'TRANSAMERICA_COMMISSION_REPORT', 'Generating Transamerica commission import file.', 'Background task status notification message.' );
INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'generatereports', 'TRANSAMERICA_ACTUARIAL_REPORT', 'Generating Transamerica actuarial import file.', 'Background task status notification message.' );



