\set db advice2pay

-- CompanyCommissionLife
create table "CompanyCommissionLife"
(
  "Id" serial not null constraint "CompanyCommissionLife_pkey" primary key,
  "CompanyId" integer not null,
  "ImportDate" date not null,
  "LifeId" integer not null,
  "CarrierId" integer not null,
  "PlanTypeId" integer not null,
  "PlanId" integer not null,
  "ImportDataId" integer
);
create index companycommissionlife_companyid_importdate_lifeid_carrierid_pla on "CompanyCommissionLife" ("CompanyId", "ImportDate", "LifeId", "CarrierId", "PlanTypeId", "PlanId");
create index companycommissionlife_companyid_importdate_index on "CompanyCommissionLife" ("CompanyId", "ImportDate");

-- CompanyCommissionLifeResearch
create table "CompanyCommissionLifeResearch"
(
  "Id" serial not null constraint "CompanyCommissionLifeResearch_pkey" primary key,
  "CompanyId" integer not null,
  "ImportDate" date not null,
  "LifeId" integer not null,
  "CarrierId" integer not null,
  "PlanTypeId" integer not null,
  "PlanId" integer not null,
  "ImportDataId" integer
);
create index companycommissionliferesearch_companyid_importdate_lifeid_carri on "CompanyCommissionLifeResearch" ("CompanyId", "ImportDate", "LifeId", "CarrierId", "PlanTypeId", "PlanId");
create index companycommissionliferesearch_companyid_importdate_index on "CompanyCommissionLifeResearch" ("CompanyId", "ImportDate");

-- CommissionType
-- Rename the commission types for display.
update "CommissionType" set "Display" = 'Heaped Flat' where "Name" = 'heap_flat';
update "CommissionType" set "Display" = 'Heaped Stack' where "Name" = 'heap_stack';

-- ReportType
-- Rename the commission report to commission detail report.
update "ReportType" set "Display" = 'Commission Detail Report' where "Name" = 'commission';