\set db advice2pay

-- Create a worker table we can use to do updates without an inner select.
create table "CompanyCommissionWorker"
(
  "Id" serial not null
    constraint "CompanyCommissionWorker_pkey"
    primary key,
  "CompanyId" integer not null,
  "ImportDate" date not null,
  "LifeId" integer,
  "CarrierId" integer,
  "PlanTypeId" integer,
  "PlanId" integer,
  "CoverageTierId" integer
)
;

create index companycommissionworker_companyid_importdate_lifeid_carrierid_p
  on "CompanyCommissionWorker" ("CompanyId", "ImportDate", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId")
;

create index "CompanyCommissionWorker_CompanyId_ImportDate_LifeId_CarrierId_P"
  on "CompanyCommissionWorker" ("CompanyId", "ImportDate", "LifeId", "CarrierId", "PlanTypeId", "PlanId")
;

create index "CompanyCommissionWorker_CompanyId_ImportDate_index"
  on "CompanyCommissionWorker" ("CompanyId", "ImportDate")
;

