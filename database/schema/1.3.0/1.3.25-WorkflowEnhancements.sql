\set db advice2pay

-- Update the old sample workflow so that it now works with the latest enhancements.
update "WorkflowState" set "Name" = 'one' where "Id" = 1;
update "WorkflowState" set "Description" = 'This is a generic workflow step does no real business logic.' where "Id" = 1;
update "WorkflowState" set "Name" = 'two' where "Id" = 2;
update "WorkflowState" set "Description" = 'This sample workflow step will not start a background task, but rather this step will be skipped while processing the workflow.' where "Id" = 2;
update "WorkflowState" set "Name" = 'three' where "Id" = 3;
update "WorkflowState" set "Description" = 'This is a generic workflow step does no real business logic.' where "Id" = 3;
delete from "WorkflowStateProperty" where "WorkflowId" = 1;
update "WorkflowProperty" set "Value" = null where "WorkflowId" = 1 and "Name" = 'WidgetJSLibrary';
update "WorkflowProperty" set "Value" = 'wf_sample_widget' where "WorkflowId" = 1 and "Name" = 'WidgetName';
insert into "WorkflowProperty" ("Id", "WorkflowId", "Name", "Value" ) values ( 3, 1, 'IdentifierType', 'company');
insert into "WorkflowProperty" ("Id", "WorkflowId", "Name", "Value" ) values ( 13, 1, 'LandingURI', 'dashboard/workflow/sample');

INSERT INTO "WorkflowStateProperty" ("Id", "WorkflowId", "WorkflowStateId", "Name", "Value") VALUES (6, 1, 2, 'Library', 'SkipMe');
INSERT INTO "WorkflowStateProperty" ("Id", "WorkflowId", "WorkflowStateId", "Name", "Value") VALUES (5, 1, 3, 'WaitingURI', 'workflow/sample/waiting');
INSERT INTO "WorkflowStateProperty" ("Id", "WorkflowId", "WorkflowStateId", "Name", "Value") VALUES (4, 1, 3, 'Library', 'WaitForIt');
