\set db advice2pay


insert into "ReportType" ( "Id", "Name", "Display", "Promote" ) values ( 6, 'transamerica_commission', 'Transamerica Commissions Export File', true);


create table "ReportTransamericaCommission"
(
  "Id" serial not null constraint "ReportTransamericaCommission_pkey" primary key,
  "CompanyId" integer not null,
  "ImportDate" date not null,
  "ImportDataId" integer not null,
  "MasterPolicy" text,
  "EmployeeNumber" text,
  "LifeId" integer not null,
  "CarrierId" integer not null,
  "PlanTypeId" integer not null,
  "PlanId" integer not null,
  "CoverageTierId" integer not null,
  "ProductType" text not null,
  "Option" text not null,
  "Tier" text not null
);
ALTER TABLE "ReportTransamericaCommission" OWNER TO :db;
CREATE INDEX trans_commission_two_idx on "ReportTransamericaCommission"("CompanyId", "ImportDate");
CREATE INDEX trans_commission_three_idx on "ReportTransamericaCommission"("CompanyId", "ImportDate", "ImportDataId");
CREATE INDEX trans_commission_nine_idx on "ReportTransamericaCommission"("CompanyId", "ImportDate", "MasterPolicy", "EmployeeNumber", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId");


create table "ReportTransamericaCommissionDetail"
(
  "Id" serial not null constraint "ReportTransamericaCommissionDetail_pkey" primary key,
  "CompanyId" integer not null,
  "ImportDate" date not null,
  "ImportDataId" integer not null,
  "LifeId" integer not null,
  "CarrierId" integer not null,
  "PlanTypeId" integer not null,
  "PlanId" integer not null,
  "CoverageTierId" integer not null,
  "ProductType" text not null,
  "Option" text not null,
  "Tier" text not null,
  "MasterPolicy" text,
  "EmployeeId" text,
  "TierEffectiveDate" date,
  "TierMonthlyPremium" text,
  "CurrentCertStatus" text,
  "OriginalCertIssueDate" date,
  "CertTermDate" date,
  "MonthPaidFor" date,
  "FirstName" text,
  "LastName" text,
  "MiddleName" text,
  "Suffix" text
);
ALTER TABLE "ReportTransamericaCommissionDetail" OWNER TO :db;