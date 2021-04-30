\set db advice2pay

-- Remove the old eligibility report files.
drop table "EligibilityReport";
drop table "EligibilityReportLives";

-- Dump the promote column.  The logic is more complex than a boolean
ALTER TABLE "ReportType" DROP COLUMN "Promote";

-- Looking up object mappings is slow.  add an index.
CREATE INDEX ObjectMapping_Code_Input_index ON "ObjectMapping" ("Code", "Input");


-- Changing the type code
update "ReportType" set "Name" = 'transamerica_eligibility' where "Id" = 5;

-- Create the new files.
create table "ReportTransamericaEligibility"
(
  "Id" serial not null constraint "ReportTransamericaEligibility_pkey" primary key,
  "CompanyId" integer not null,
  "ImportDate" date not null,
  "ImportDataId" integer not null,
  "LifeId" integer not null,
  "CarrierId" integer not null,
  "PlanTypeId" integer not null,
  "PlanId" integer not null,
  "CoverageTierId" integer not null,
  "EmployeeNumber" text,
  "ProductType" text not null,
  "Option" text not null,
  "Tier" text not null,
  "LostItem" boolean default false not null
);
ALTER TABLE "ReportTransamericaEligibility" OWNER TO :db;
CREATE INDEX transamerica_eligibility_two_idx on "ReportTransamericaEligibility"("CompanyId", "ImportDate");
CREATE INDEX transamerica_eligibility_three_idx on "ReportTransamericaEligibility"("CompanyId", "ImportDate", "ImportDataId");
CREATE INDEX transamerica_eligibility_eight_idx on "ReportTransamericaEligibility"("CompanyId", "ImportDate", "EmployeeNumber", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId");


create table "ReportTransamericaEligibilityDetails"
(
  "Id" serial not null constraint "ReportTransamericaEligibilityDetails_pkey" primary key,
  "CompanyId" integer not null,
  "ImportDate" date not null,
  "ImportDataId" integer not null,
  "LifeId" integer not null,
  "CarrierId" integer not null,
  "PlanTypeId" integer not null,
  "PlanId" integer not null,
  "CoverageTierId" integer not null,
  "EmployeeNumber" text,
  "EmployeeGroupNumber" text,
  "AddressLine1" text,
  "AddressLine2" text,
  "City" text,
  "State" text,
  "ZipCode" text,
  "ZipCodeExpansion" text,
  "CountryCode" text,
  "PhoneNumber" text,
  "IssueState" text,
  "PaidToDate" date,
  "RelationshipCode" text,
  "Status" text,
  "FirstName" text,
  "LastName" text,
  "MiddleInitial" text,
  "EffectiveDate" date,
  "TerminationDate" date,
  "DateOfBirth" date,
  "Gender" text,
  "CreditableCoverage" text,
  "IndemnityAmount" text,
  "ProductType" text not null,
  "Option" text not null,
  "Tier" text not null,
  "SortId" integer
);
ALTER TABLE "ReportTransamericaEligibilityDetails" OWNER TO :db;
CREATE INDEX transamerica_eligibilitydetails_two_idx on "ReportTransamericaEligibilityDetails"("CompanyId", "ImportDate");
CREATE INDEX transamerica_eligibilitydetails_three_idx on "ReportTransamericaEligibilityDetails"("CompanyId", "ImportDate", "ImportDataId");
CREATE INDEX transamerica_eligibilitydetails_seven_idx on "ReportTransamericaEligibilityDetails"("CompanyId", "ImportDate", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId", "EmployeeNumber");

