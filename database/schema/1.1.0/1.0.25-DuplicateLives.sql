\set db advice2pay

create table "ImportDataDuplicateLives"
(
  "Id" serial not null constraint "ImportDataDuplicateLives_pkey" primary key,
  "CompanyId" integer not null,
  "ImportDate" date not null,
  "Count" integer,
  "Rows" text,
  "EmployeeId" text,
  "SSN" text,
  "FirstName" text,
  "DateOfBirth" text,
  "Relationship" text,
  "CarrierId" integer,
  "PlanTypeId" integer,
  "PlanId" integer,
  "CoverageTierId" integer
);
ALTER TABLE "ObjectMapping" OWNER TO :db;