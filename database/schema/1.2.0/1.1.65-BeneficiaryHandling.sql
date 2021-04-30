\set db advice2pay

-- CompanyBeneficiaryMap
-- This table holds mappings between a user description and our beneficiary codes.
create table "CompanyBeneficiaryMap"
(
    "Id" bigserial not null constraint "CompanyBeneficiaryMapId" primary key,
    "CompanyId" integer not null,
    "ColumnCode" text not null,
    "BeneficiaryNormalized" text not null,
    "UserDescription" text not null
);
-- select * from "CompanyBeneficiaryMap"


-- CompanyParentBeneficiaryMap
-- This table holds mappings between a user description and our beneficiary codes for a parent.
create table "CompanyParentBeneficiaryMap"
(
    "Id" bigserial not null constraint "CompanyParentBeneficiaryMapId" primary key,
    "CompanyParentId" integer not null,
    "ColumnCode" text not null,
    "BeneficiaryNormalized" text not null,
    "UserDescription" text not null
);
-- select * from "CompanyParentBeneficiaryMap"



-- CompanyBeneficiaryImport
-- This table holds import data that has been identified as containing beneficiaries.
create table "CompanyBeneficiaryImport"
(
    "Id" bigserial not null constraint "CompanyBeneficiaryImportId" primary key,
    "CompanyId" integer not null,
    "ImportDate" date not null,
    "Finalized" boolean default false not null,
    "RowNumber" integer not null,
    "EmployeeId" text,
    "PlanType" text,
    "FirstName" text,
    "LastName" text,
    "CoverageStartDate" date,
    "CoverageEndDate" date,
    "AnnualSalary" numeric(18,4),
    "Carrier" text,
    "CoverageTier" text,
    "DateOfBirth" date,
    "MonthlyCost" numeric(18,4),
    "EmploymentActive" boolean,
    "EmploymentEnd" date,
    "EmploymentStart" date,
    "MiddleName" text,
    "Gender" char,
    "Plan" text,
    "SSN" text,
    "SSNDisplay" text,
    "TobaccoUser" boolean,
    "Volume" numeric(18,4),
    "Relationship" text,
    "Reason" text,
    "Address1" text,
    "Address2" text,
    "City" text,
    "State" text,
    "PostalCode" text,
    "Phone1" text,
    "Phone2" text,
    "Email1" text,
    "Email2" text,
    "Suffix" text,
    "Division" text,
    "Department" text,
    "BusinessUnit" text,
    "OriginalEffectiveDate" date,
    "Policy" text,
    "GroupNumber" text,
    "EnrollmentState" text,
    "EmployeeSSN" text,
    "EmployeeSSNDisplay" text
);
create index companybeneficiaryimport_id_idx on "CompanyBeneficiaryImport" ("Id");
create index companybeneficiaryimport_companyid_importdate_idx on "CompanyBeneficiaryImport" ("CompanyId", "ImportDate");
-- select * from "CompanyBeneficiaryImport"




-- FEATURE
-- Add the BENEFICIARY_MAPPING feature.
INSERT INTO "Feature" ( "Id", "Code", "CompanyParentFlg", "CompanyFlg", "Description", "Targetable", "TargetType" ) values ( 10, 'BENEFICIARY_MAPPING', false, true, 'features/beneficiary_mapping', true, 'mapping_column' );