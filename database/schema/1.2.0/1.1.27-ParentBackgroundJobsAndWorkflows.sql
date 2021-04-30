\set db advice2pay

-- Workflow
-- Table to keep track of named workflows.
create table if not exists "Workflow"
(
    "Id" integer not null constraint "Workflow_pkey1" primary key,
    "Name" text,
    "Description" text
);
create unique index "WorkflowId_uindex" on "Workflow" ("Id");
INSERT INTO "Workflow" ("Id", "Name", "Description") VALUES (1, 'sample', 'Sample Workflow');
INSERT INTO "Workflow" ("Id", "Name", "Description") VALUES (2, 'parent_import_csv', 'Parent Upload CSV Workflow');


-- WorkflowProgress
-- Table used to keep track on workflows currently in progress.
create table "WorkflowProgress"
(
    "Id" serial not null constraint "WorkflowProgress_pkey" primary key,
    "Identifier" integer,
    "IdentifierType" text,
    "UserId" integer,
    "WorkflowId" integer not null,
    "WorkflowStateId" integer,
    "ActionDate" date default now() not null,
    "Running" boolean default false not null,
    "Complete" boolean default false,
    "Waiting" boolean default false,
    "Failed" boolean default false
);

-- WorkflowProgressProperty
-- Create a table where we can keep runtime data for a workflow that is
-- in progress.  Will only live for the life cycle of the workflow.
create table "WorkflowProgressProperty"
(
    "Id" serial not null constraint "WorkflowProgressProperty_pkey" primary key,
    "WorkflowId" integer not null,
    "Identifier" integer not null,
    "IdentifierType" text not null,
    "Name" text not null,
    "Value" text
);


-- WorkflowProperty
-- Table to hold custom properties for a workflow.
create table "WorkflowProperty"
(
    "Id" serial not null constraint "WorkflowProperty_pkey" primary key,
    "WorkflowId" integer not null,
    "Name" text not null,
    "Value" text
);
INSERT INTO "WorkflowProperty" ("Id", "WorkflowId", "Name", "Value") VALUES (2, 1, 'WidgetName', 'sample_workflow_widget');
INSERT INTO "WorkflowProperty" ("Id", "WorkflowId", "Name", "Value") VALUES (4, 1, 'WidgetRefreshCallback', 'InitLoadingButtons');
INSERT INTO "WorkflowProperty" ("Id", "WorkflowId", "Name", "Value") VALUES (1, 1, 'WidgetJSLibrary', 'widget.js');
INSERT INTO "WorkflowProperty" ("Id", "WorkflowId", "Name", "Value") VALUES (6, 2, 'WidgetRefreshCallback', 'InitLoadingButtons');
INSERT INTO "WorkflowProperty" ("Id", "WorkflowId", "Name", "Value") VALUES (8, 2, 'IdentifierType', 'companyparent');
INSERT INTO "WorkflowProperty" ("Id", "WorkflowId", "Name", "Value") VALUES (5, 2, 'WidgetName', 'wf_parent_import_csv_widget');
INSERT INTO "WorkflowProperty" ("Id", "WorkflowId", "Name", "Value") VALUES (7, 2, 'WidgetJSLibrary', 'widget.js');


-- WorkflowState
-- Table defines each state/step in a workflow.
create table "WorkflowState"
(
    "Id" integer not null constraint "WorkflowState_pkey1" primary key,
    "WorkflowId" integer not null,
    "Name" text not null,
    "Description" text not null,
    "NextStateId" integer
);
create unique index "WorkflowStateId_uindex" on "WorkflowState" ("Id");
INSERT INTO "WorkflowState" ("Id", "WorkflowId", "Name", "Description", "NextStateId") VALUES (1, 1, 'step1', 'Sample Step 1', 2);
INSERT INTO "WorkflowState" ("Id", "WorkflowId", "Name", "Description", "NextStateId") VALUES (2, 1, 'step2', 'Sample Step 2', 3);
INSERT INTO "WorkflowState" ("Id", "WorkflowId", "Name", "Description", "NextStateId") VALUES (3, 1, 'step3', 'Sample Step 3', null);
INSERT INTO "WorkflowState" ("Id", "WorkflowId", "Name", "Description", "NextStateId") VALUES (120, 2, 'split', 'Split Parent CSV', null);
INSERT INTO "WorkflowState" ("Id", "WorkflowId", "Name", "Description", "NextStateId") VALUES (110, 2, 'map', 'Company Mapping', 120);
INSERT INTO "WorkflowState" ("Id", "WorkflowId", "Name", "Description", "NextStateId") VALUES (104, 2, 'validate', 'Validate Data', 110);
INSERT INTO "WorkflowState" ("Id", "WorkflowId", "Name", "Description", "NextStateId") VALUES (100, 2, 'parse', 'Parse Parent CSV', 104);

-- WorkflowStateOrder
-- Table used to order states in a workflow.
create table "WorkflowStateOrder"
(
    "Id" serial not null constraint "WorkflowStateOrder_pkey" primary key,
    "WorkflowId" integer not null,
    "WorkflowStateId" integer not null,
    "SortOrder" integer not null
);
INSERT INTO "WorkflowStateOrder" ("Id", "WorkflowId", "WorkflowStateId", "SortOrder") VALUES (1, 1, 1, 1);
INSERT INTO "WorkflowStateOrder" ("Id", "WorkflowId", "WorkflowStateId", "SortOrder") VALUES (2, 1, 2, 2);
INSERT INTO "WorkflowStateOrder" ("Id", "WorkflowId", "WorkflowStateId", "SortOrder") VALUES (3, 1, 3, 3);
INSERT INTO "WorkflowStateOrder" ("Id", "WorkflowId", "WorkflowStateId", "SortOrder") VALUES (4, 2, 100, 1);
INSERT INTO "WorkflowStateOrder" ("Id", "WorkflowId", "WorkflowStateId", "SortOrder") VALUES (6, 2, 110, 3);
INSERT INTO "WorkflowStateOrder" ("Id", "WorkflowId", "WorkflowStateId", "SortOrder") VALUES (5, 2, 104, 2);
INSERT INTO "WorkflowStateOrder" ("Id", "WorkflowId", "WorkflowStateId", "SortOrder") VALUES (7, 2, 120, 4);


-- WorkflowStateProperty
-- Table to hold custom state properties
create table "WorkflowStateProperty"
(
    "Id" serial not null constraint "WorkflowStateProperty_pkey" primary key,
    "WorkflowId" integer not null,
    "WorkflowStateId" integer not null,
    "Name" text not null,
    "Value" text
);
INSERT INTO "WorkflowStateProperty" ("Id", "WorkflowId", "WorkflowStateId", "Name", "Value") VALUES (7, 1, 1, 'BackgroundClass', 'SampleWorkflowStep1');
INSERT INTO "WorkflowStateProperty" ("Id", "WorkflowId", "WorkflowStateId", "Name", "Value") VALUES (8, 1, 2, 'BackgroundClass', 'SampleWorkflowStep2');
INSERT INTO "WorkflowStateProperty" ("Id", "WorkflowId", "WorkflowStateId", "Name", "Value") VALUES (9, 1, 3, 'BackgroundClass', 'SampleWorkflowStep3');
INSERT INTO "WorkflowStateProperty" ("Id", "WorkflowId", "WorkflowStateId", "Name", "Value") VALUES (13, 2, 100, 'WaitingURI', 'parent/match');
INSERT INTO "WorkflowStateProperty" ("Id", "WorkflowId", "WorkflowStateId", "Name", "Value") VALUES (15, 2, 100, 'Library', 'ParseCSVUploadFile');
INSERT INTO "WorkflowStateProperty" ("Id", "WorkflowId", "WorkflowStateId", "Name", "Value") VALUES (16, 2, 104, 'Library', 'ValidateCSVUploadFile');
INSERT INTO "WorkflowStateProperty" ("Id", "WorkflowId", "WorkflowStateId", "Name", "Value") VALUES (17, 2, 104, 'Controller', 'ParentUploadValidateCSV');
INSERT INTO "WorkflowStateProperty" ("Id", "WorkflowId", "WorkflowStateId", "Name", "Value") VALUES (18, 2, 100, 'VerbiageGroup', 'parsecsvupload');
INSERT INTO "WorkflowStateProperty" ("Id", "WorkflowId", "WorkflowStateId", "Name", "Value") VALUES (10, 2, 100, 'Controller', 'ParentUploadParseCSV');
INSERT INTO "WorkflowStateProperty" ("Id", "WorkflowId", "WorkflowStateId", "Name", "Value") VALUES (11, 2, 110, 'Controller', 'ParentUploadMapCompanies');
INSERT INTO "WorkflowStateProperty" ("Id", "WorkflowId", "WorkflowStateId", "Name", "Value") VALUES (12, 2, 120, 'Controller', 'ParentUploadSplitCSV');
INSERT INTO "WorkflowStateProperty" ("Id", "WorkflowId", "WorkflowStateId", "Name", "Value") VALUES (19, 2, 104, 'WaitingURI', 'parent/correct');
INSERT INTO "WorkflowStateProperty" ("Id", "WorkflowId", "WorkflowStateId", "Name", "Value") VALUES (20, 2, 104, 'VerbiageGroup', 'ValidateCSVUpload');
INSERT INTO "WorkflowStateProperty" ("Id", "WorkflowId", "WorkflowStateId", "Name", "Value") VALUES (21, 2, 110, 'Library', 'MapCompanyCSVUploadFile');
INSERT INTO "WorkflowStateProperty" ("Id", "WorkflowId", "WorkflowStateId", "Name", "Value") VALUES (22, 2, 110, 'VerbiageGroup', 'MapCompanyCSVUpload');
INSERT INTO "WorkflowStateProperty" ("Id", "WorkflowId", "WorkflowStateId", "Name", "Value") VALUES (14, 2, 110, 'WaitingURI', 'parent/map/company');
INSERT INTO "WorkflowStateProperty" ("Id", "WorkflowId", "WorkflowStateId", "Name", "Value") VALUES (23, 2, 120, 'VerbiageGroup', 'SplitCompanyCSVUpload');
INSERT INTO "WorkflowStateProperty" ("Id", "WorkflowId", "WorkflowStateId", "Name", "Value") VALUES (24, 2, 120, 'Library', 'SplitCompanyCSVUpload');




