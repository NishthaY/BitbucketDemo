\set db advice2pay


-- Add the CompanyParentId column to the ProcessQueue table.
DO $$
BEGIN
  BEGIN
    ALTER TABLE "ProcessQueue" ADD "CompanyParentId" INT NULL;
    EXCEPTION
    WHEN duplicate_column THEN RAISE NOTICE 'column GroupId already exists in ProcessQueue.';
  END;
END;
$$;



INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'parentuploadimport', 'EMPTY_STRING', '', 'Background task status notification message.' );
INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'parentuploadimport', 'START', 'Uploading and securing import.', 'Background task status notification message.' );

INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'parentuploadparsecsv', 'EMPTY_STRING', '', 'Background task status notification message.' );
INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'parentuploadparsecsv', 'START', 'Parsing data file.', 'Background task status notification message.' );

INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'parentuploadmapcompanies', 'EMPTY_STRING', '', 'Background task status notification message.' );
INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'parentuploadmapcompanies', 'START', 'Searching for and researching companies.', 'Background task status notification message.' );

INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'parentuploadsplitcsv', 'EMPTY_STRING', '', 'Background task status notification message.' );
INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'parentuploadsplitcsv', 'START', 'Splitting apart import by company', 'Background task status notification message.' );


-- CompanyParentMappingColumn
-- Create a new table to hold the mappings for the company parent table.
CREATE TABLE "CompanyParentMappingColumn"
(
    "CompanyParentId" INT NOT NULL,
    "Name" text not null,
    "Display" text not null,
    "Required" boolean default false not null,
    "DefaultValue" text,
    "ColumnName" text,
    "Encrypted" boolean default false,
    "Conditional" boolean default false,
    "ConditionalList" text,
    "NormalizationRegEx" text
)
WITH (
   OIDS=FALSE
);


-- Create a new table to hold the best matched data for the company parent.
CREATE TABLE "CompanyParentBestMappedColumn"
(
    "Id" bigserial NOT NULL,
    "CompanyParentId" integer NOT NULL,
    "ColumnName" text NULL,
    "ColumnNameNormalized" text NULL,
    "MappingColumnName" text NULL,
    "ColumnNumber" integer NULL,
    CONSTRAINT "CompanyParentBestMappedColumnId" PRIMARY KEY ("Id")
)
    WITH (
        OIDS=FALSE
    );

-- Create the company column
insert into "MappingColumns" ( "Name", "Display", "Required" ) values ( 'company', 'Company', false );
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'company', 'Company');

-- Alter validation errors so we can write errors against the company parent.
alter table "ValidationErrors" add "CompanyParentId" int;

-- Alter HistoryEmail table so it can store emails bound for parents.
alter table "HistoryEmail" add "CompanyParentId" int;

-- Allow the CompanyId to be null in the ValidationErrors table now that
-- we can have records by CompanyParentId only.
alter table "ValidationErrors" alter column "CompanyId" drop not null;

-- HistoryChangeToCompanyParent
-- Start tracking who moves to which company parent for the admin dashboard.
create table "HistoryChangeToCompanyParent"
(
    "Id" bigserial not null constraint "HistoryChangeToCompanyParentId" primary key,
    "UserId" integer not null,
    "CompanyParentId" integer not null,
    "ChangedToDate" timestamp with time zone default now() not null
        constraint "HistoryChangeToCompanyParent_ChangedToDate_check"
            check (date_part('timezone'::text, "ChangedToDate") = '0'::double precision)
);

create index historychangetocompanyparent_id_idx on "HistoryChangeToCompanyParent" ("Id");
create index historychangetocompanyparent_userid_idx on "HistoryChangeToCompanyParent" ("UserId");
create index historychangetocompanyparent_userid_companyid_idx on "HistoryChangeToCompanyParent" ("UserId", "CompanyParentId");


-- CompanyParentMapCompany
create table "CompanyParentMapCompany"
(
    "Id" serial not null constraint "CompanyParentMapCompany_pk" primary key,
    "CompanyParentId" integer not null,
    "CompanyNormalized" text,
    "UserDescription" text,
    "CompanyId" integer,
    "Ignored" boolean default false
);

-- CompanyParentImportData
create table "CompanyParentImportData"
(
    "Id" serial not null constraint "CompanyParentImportData_pk" primary key,
    "CompanyParentId" integer not null,
    "Company" text
);





