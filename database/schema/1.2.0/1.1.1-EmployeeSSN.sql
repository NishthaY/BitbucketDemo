\set db advice2pay

-- No longer require the Employee ID column.
update "MappingColumns" set "Required" = false where "Name" = 'eid';
update "CompanyMappingColumn" set "Required" = false where "Name" = 'eid';

-- Rename the 'SSN' column to 'Personal SSN'
update "MappingColumns" set "Display" = 'Personal SSN' where "Name" = 'ssn';
update "CompanyMappingColumn" set "Display" = 'Personal SSN' where "Name" = 'ssn';

-- Add a new column called 'Employee SSN'
insert into "MappingColumns" ( "Name", "Display", "Required", "Encrypted" ) values ( 'employee_ssn', 'Employee SSN', false, true );
insert into "MappingColumnHeaders" ( "Name", "Header" ) values ( 'employee_ssn', 'Employee SSN');
ALTER TABLE "ImportData" ADD COLUMN "EmployeeSSN" text NULL;
ALTER TABLE "ImportData" ADD COLUMN "EmployeeSSNDisplay" text NULL;

-- Add the Conditional and ConditionalList columns to the MappingColumns table.
ALTER TABLE "MappingColumns" ADD "Conditional" BOOLEAN DEFAULT FALSE NULL;
ALTER TABLE "MappingColumns" ADD "ConditionalList" TEXT NULL;
update "MappingColumns" set "Conditional" = false;

-- Add the Conditional and ConditionalList columns to the CompanyMappingColumns table.
ALTER TABLE "CompanyMappingColumn" ADD "Conditional" BOOLEAN DEFAULT FALSE NULL;
ALTER TABLE "CompanyMappingColumn" ADD "ConditionalList" TEXT NULL;
update "CompanyMappingColumn" set "Conditional" = false;

-- Make the eid and employee_ssn fields conditional on each other.
update "MappingColumns" set "Conditional" = true, "ConditionalList" = 'eid,employee_ssn' where "Name" = 'eid';
update "CompanyMappingColumn" set "Conditional" = true, "ConditionalList" = 'eid,employee_ssn' where "Name" = 'eid';

update "MappingColumns" set "Conditional" = true, "ConditionalList" = 'eid,employee_ssn' where "Name" = 'employee_ssn';
update "CompanyMappingColumn" set "Conditional" = true, "ConditionalList" = 'eid,employee_ssn' where "Name" = 'employee_ssn';

-- Turn on the extension that will allow us to create UUID values.
-- Select one now so I can see in the logs if it worked or not on first deploy.
create extension if not exists "uuid-ossp";
select uuid_generate_v4();

-- The EmployeeID is no longer required, pull that out of the
-- report properties table.
delete from "ReportProperties" where "Key" = 'eid';

-- CompanyUniversalEmployee
-- This table will hold a 'mapping between the EmployeeSSN and
-- an internal Universal EID that we generate.  To be used when
-- a customer does not provide EID.
create table "CompanyUniversalEmployee"
(
  "Id" serial not null constraint "CompanyUniversalEmployee_pkey" primary key,
  "CompanyId" integer not null,
  "EmployeeSSN" text not null,
  "UniversalEmployeeId" text not null,
  "DiscoveryDate" date not null,
  "Finalized" boolean default false not null
)
;
CREATE INDEX CompanyUniversalEmployee_CompanyId_EmployeeSSN_index ON public."CompanyUniversalEmployee" ("CompanyId", "EmployeeSSN");

-- Commission Report
-- The commission report requires that EmployeeSSN column is required, not conditional.
insert into "ReportProperties" ( "ReportCode", "Group", "Key", "Value") values ( 'transamerica_commission', 'REQUIRED_COLUMN', 'employee_ssn', 'TRUE');

-- Eligibility Report
-- Add a new field to the output.
ALTER TABLE "ReportTransamericaEligibilityDetails" ADD "EmployeeSSN" TEXT NULL;

-- Create a table that will allow us to rollback our changes to the ImportData table
-- When we apply a universal id.
CREATE TABLE "CompanyUniversalEmployeeRollback"
(
  "Id" serial not null constraint "CompanyUniversalEmployeeRollback_pkey" primary key,
  "CompanyId" integer not null,
  "ImportDate" date not null,
  "ImportDataId" integer not null,
  "OriginalEmployeeId" text
);
CREATE INDEX CompanyUniversalEmployeeRollback_CompanyId_ImportDate_ImportDataId_index ON public."CompanyUniversalEmployeeRollback" ("CompanyId", "ImportDate", "ImportDataId");
CREATE INDEX CompanyUniversalEmployeeRollback_CompanyId_ImportDate_index ON public."CompanyUniversalEmployeeRollback" ("CompanyId", "ImportDate");