\set db advice2pay


-- Create the new files.
create table "ReportTransamericaActuarial"
(
  "Id" serial not null constraint "ReportTransamericaActuarial_pkey" primary key,
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
ALTER TABLE "ReportTransamericaActuarial" OWNER TO :db;
CREATE INDEX transamerica_actuarial_two_idx on "ReportTransamericaActuarial"("CompanyId", "ImportDate");
CREATE INDEX transamerica_actuarial_three_idx on "ReportTransamericaActuarial"("CompanyId", "ImportDate", "ImportDataId");
CREATE INDEX transamerica_actuarial_eight_idx on "ReportTransamericaActuarial"("CompanyId", "ImportDate", "EmployeeNumber", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId");
