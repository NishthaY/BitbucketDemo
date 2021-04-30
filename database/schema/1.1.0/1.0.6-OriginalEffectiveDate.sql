\set db advice2pay


-- LifeOriginalEffectiveDate
create table "LifeOriginalEffectiveDate"
(
  "LifeId" integer not null,
  "CarrierId" integer not null,
  "PlanTypeId" integer not null,
  "PlanId" integer not null,
  "CoverageTierId" integer not null,
  "EffectiveDate" date not null,
  "DiscoveryDate" date not null,
  "IsCoverageStartDate" boolean default false not null,
  "LostDate" date,
  constraint "LifeOriginalEffectiveDate_pkey"
  primary key ("LifeId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId")
)
;
create index lifecoverage_idx
  on "LifeOriginalEffectiveDate" ("LifeId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId")
;



-- LifeOriginalEffectiveDateCompare
create table "LifeOriginalEffectiveDateCompare"
(
  "CompanyId" integer not null,
  "ImportDate" date not null,
  "LifeId" integer not null,
  "CarrierId" integer not null,
  "PlanTypeId" integer not null,
  "PlanId" integer not null,
  "CoverageTierId" integer not null,
  "CoverageStartDate" date not null,
  "OriginalEffectiveDate" date,
  "Calculated-EffectiveDate" date,
  "IsCoverageStartDate" boolean,
  "Code" text,
  "Description" text,
  "LostDate" date,
  "ImportDataId" integer
)
;
create index lifeoriginaleffectivedatecompare_companyid_importdate_lifeid_ca
  on "LifeOriginalEffectiveDateCompare" ("CompanyId", "ImportDate", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId")
;
create index lifeoriginaleffectivedatecompare_companyid_importdate_code_inde
  on "LifeOriginalEffectiveDateCompare" ("CompanyId", "ImportDate", "Code")
;



-- LifeOriginalEffectiveDateRollback
create table "LifeOriginalEffectiveDateRollback"
(
  "CompanyId" integer not null,
  "ImportDate" date not null,
  "LifeId" integer not null,
  "CarrierId" integer not null,
  "PlanTypeId" integer not null,
  "PlanId" integer not null,
  "CoverageTierId" integer not null,
  "EffectiveDate" date,
  "DiscoveryDate" date,
  "IsCoverageStartDate" boolean,
  "LostDate" date,
  "Code" text not null
)
;
create index lifeoriginaleffectivedaterollback_companyid_importdate_code_ind
  on "LifeOriginalEffectiveDateRollback" ("CompanyId", "ImportDate", "Code")
;


ALTER TABLE "LifeOriginalEffectiveDate" OWNER TO :db;
ALTER TABLE "LifeOriginalEffectiveDateCompare" OWNER TO :db;
ALTER TABLE "LifeOriginalEffectiveDateRollback" OWNER TO :db;