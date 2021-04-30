\set db advice2pay

INSERT INTO "Feature" ( "Code", "CompanyParentFlg", "CompanyFlg", "Description" ) values ( 'COMMISSION_TRACKING', true, true, 'features/commission_tracking' )
ON CONFLICT DO NOTHING ;

INSERT INTO "Feature" ( "Code", "CompanyParentFlg", "CompanyFlg", "Description" ) values ( 'ORIGINAL_EFFECTIVE_DATE_TIER_CHANGE', true, true, 'features/oed_variant1' )
ON CONFLICT DO NOTHING ;


-- Commission Report
-- Add a new A2P report.
insert into "ReportType" ( "Id", "Name", "Display" ) values (9, 'commission', 'Commission Report');

-- Let Pusher know what to say when we try and create a warnings report.
INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'generatereports', 'COMMISSION_DATA', 'Generating commission data.', 'Background task status notification message.' );
INSERT INTO "Verbiage" ( "Group", "Key", "Verbiage", "Notes") VALUES ( 'generatereports', 'GENERATING_A2P_COMMISSION_REPORT', 'Generating commission report.', 'Background task status notification message.' );

-- ReportTransamericaCommissionDetail
-- Add two new columns.
ALTER TABLE "ReportTransamericaCommissionDetail" ADD "PremiumFirstYear" NUMERIC(18,4) NULL;
ALTER TABLE "ReportTransamericaCommissionDetail" ADD "PremiumRenewal" NUMERIC(18,4) NULL;

-- LifeOriginalEffectiveDateCompare
-- Add a new column to keep track of the oldest life plan effective date.
ALTER TABLE "LifeOriginalEffectiveDateCompare" ADD "OldestLifePlanEffectiveDate" DATE NULL;
ALTER TABLE "LifeOriginalEffectiveDateCompare" ADD "OEDReset" BOOLEAN NULL;
ALTER TABLE "LifeOriginalEffectiveDateCompare" ADD "OldestLifePlanDiscoveryDate" DATE NULL;

-- CompanyCommissionData
-- Table to help generate commission data.
create table "CompanyCommissionData"
(
  "Id" serial not null constraint "CompanyCommissionData_pkey" primary key,
  "CompanyId" integer not null,
  "ImportDate" date not null,
  "LifeId" integer not null,
  "CarrierId" integer not null,
  "PlanTypeId" integer not null,
  "PlanId" integer not null,
  "CoverageTierId" integer not null,
  "OEDCode" text not null,
  "MonthlyCost" numeric(18,4),
  "Volume" numeric(18,4),
  "Calculated-EffectiveDate" date,
  "CoverageStartDate" date,
  "LostDate" date,
  "Before-CoverageStartDate" date,
  "OEDReset" boolean
);
create index companycommissiondata_companyid_importdateid_lifeid_carrierid_p on "CompanyCommissionData" ("CompanyId", "ImportDate", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId");
create index companycommissiondata_companyid_importdate_index on "CompanyCommissionData" ("CompanyId", "ImportDate");



-- CompanyCommissionDataCompare
-- Table to help decide what to do with each life billing record for the month.
create table "CompanyCommissionDataCompare"
(
  "Id" serial not null constraint "CompanyCommissionDataCompare_pkey" primary key,
  "CompanyId" integer not null,
  "ImportDate" date not null,
  "LifeId" integer not null,
  "CarrierId" integer not null,
  "PlanTypeId" integer not null,
  "PlanId" integer not null,
  "CoverageTierId" integer not null,
  "Code" text,
  "Description" text,
  "OEDReset" boolean,
  "TierChanged" boolean,
  "VolumeChanged" boolean,
  "MonthlyCostChanged" boolean,
  "CoverageStartDateChanged" boolean,
  "VolumeIncreased" boolean,
  "MonthlyCostIncreased" boolean
);
create index companycommissiondatacompare_companyid_importdate_code_carrieri on "CompanyCommissionDataCompare" ("CompanyId", "ImportDate", "Code", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId");
create index companycommissiondatacompare_companyid_importdate_code_index on "CompanyCommissionDataCompare" ("CompanyId", "ImportDate", "Code");
create index companycommissiondatacompare_companyid_importdate__index on "CompanyCommissionDataCompare" ("CompanyId", "ImportDate");


-- Company Commission
-- Table to hold the final commission data, once processed, for the month.
create table "CompanyCommission"
(
  "Id" serial not null constraint "CompanyCommission_""Id""_pk" primary key,
  "CompanyId" integer not null,
  "ImportDate" date not null,
  "LifeId" integer not null,
  "CarrierId" integer not null,
  "PlanTypeId" integer not null,
  "PlanId" integer,
  "CommissionEffectiveDate" date,
  "CommissionablePremium" numeric(18,4),
  "ResetRecord" boolean default false
);
create index companycommission_companyid_importdate_lifeid_carrierid_plantyp on "CompanyCommission" ("CompanyId", "ImportDate", "LifeId", "CarrierId", "PlanTypeId", "PlanId");
create index companycommission_companyid_importdate_index on "CompanyCommission" ("CompanyId", "ImportDate");

-- CompanyCommissionSummary
-- Keep track so summerized data from the commission table.
create table "CompanyCommissionSummary"
(
  "Id" serial not null constraint "CompanyCommissionSummary_pkey" primary key,
  "CompanyId" integer not null,
  "ImportDate" date not null,
  "LifeId" integer not null,
  "CarrierId" integer not null,
  "PlanTypeId" integer not null,
  "PlanId" integer not null,
  "CommissionablePremiumTotal" numeric(18,4),
  "CommissionablePremiumAgedMoreThanOneYear" numeric(18,4),
  "CommissionablePremiumAgedOneYearOrLess" numeric(18,4)
);
create index companycommissionsummary_companyid_importdate_lifeid_carrierid_ on "CompanyCommissionSummary" ("CompanyId", "ImportDate", "LifeId", "CarrierId", "PlanTypeId", "PlanId");
create index companycommissionsummary_companyid_importdate_index on "CompanyCommissionSummary" ("CompanyId", "ImportDate");

-- CompanyCommissionWarning
-- Table to keep a list of issues as we process.
create table "CompanyCommissionWarning"
(
  "Id" serial not null constraint "CommissionWarning_pkey" primary key,
  "CompanyId" integer not null,
  "ImportDate" date not null,
  "ImportDataId" integer,
  "Tag" text,
  "Issue" text,
  "Internal" boolean default false not null
);

-- Commission Type
-- Keeps track of the different commission calculation types.
create table "CommissionType"
(
  "Id" integer not null constraint commissiontype_pkey primary key,
  "Name" text,
  "Display" text
);
insert into "CommissionType" ("Id", "Name", "Display" ) values ( 1, 'level', 'Level');
insert into "CommissionType" ("Id", "Name", "Display" ) values ( 2, 'heap_flat', 'Heap Flat');
insert into "CommissionType" ("Id", "Name", "Display" ) values ( 3, 'heap_stack', 'Heap Stack');



