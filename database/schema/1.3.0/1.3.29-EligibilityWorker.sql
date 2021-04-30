\set db advice2pay


-- Create a worker table for the eligibility details table.
create table "ReportTransamericaEligibilityDetailsWorker"
(
    "Id" serial not null constraint """ReportTransamericaEligibilityDetailsWorker""_pk" primary key,
    "CompanyId" integer not null,
    "ImportDate" date not null,
    "EmployeeNumber" text not null,
    "LifeId" integer,
    "CarrierId" integer not null,
    "PlanTypeId" integer not null,
    "PlanId" integer not null,
    "CoverageTierId" integer not null,
    "RelationshipCode" text not null,
    "Ignore" boolean default false not null
);
create index reporttransamericaeligibilitydetailsworker_companyid_importdate on "ReportTransamericaEligibilityDetailsWorker" ("CompanyId", "ImportDate");
create index ReportTransamericaEligibilityDetailsWorker_Big_index on "ReportTransamericaEligibilityDetailsWorker" ("CompanyId", "ImportDate", "EmployeeNumber", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId");