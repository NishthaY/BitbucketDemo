\set db advice2pay

-- Add the new report type code.
insert into "ReportType" ( "Id", "Name", "Display" ) values ( 7, 'transamerica_actuarial', 'Transamerica Actuarial Export File');

-- Create a table to hold the report details.
create table "ReportTransamericaActuarialDetails"
(
  "Id" serial not null constraint "ReportTransamericaActuarialDetails_pkey" primary key,
  "CompanyId" integer not null,
  "ImportDate" date not null,
  "ImportDataId" integer not null,
  "LifeId" integer not null,
  "CarrierId" integer not null,
  "PlanTypeId" integer not null,
  "PlanId" integer not null,
  "CoverageTierId" integer not null,
  "EmployeeNumber" text,

  "PolicyNumber" text,
  "GroupNumber" text,
  "ResidentState" text,
  "StatusCode" text,
  "IssueDate" date,
  "PaidToDate" date,
  "SystemTerminationDate" date,
  "BillingMode" text,
  "ModalPremium" text,
  "InsuredDOB" text,
  "InsuredSex" text,
  "InsuredState" text,
  "InsuredZIP" text,
  "InsuredSSN" text,
  "InsuredFirstName" text,
  "InsuredLastName" text,
  "ProductType" text not null,
  "Option" text not null,
  "Tier" text not null
)
;
ALTER TABLE "ReportTransamericaActuarialDetails" OWNER TO :db;
create index transamerica_actuarialdetails_three_idx on "ReportTransamericaActuarialDetails" ("CompanyId", "ImportDate", "ImportDataId");
create index transamerica_actuarialdetails_two_idx on "ReportTransamericaActuarialDetails" ("CompanyId", "ImportDate");
create index transamerica_actuarialdetails_dataid_idx on "ReportTransamericaActuarialDetails" ("ImportDataId");

