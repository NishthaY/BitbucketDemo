\set db advice2pay

-- Create a table to hold "features" that we can turn on and off.
CREATE TABLE "Feature"
(
  "Id" serial not null constraint "Feature_pkey" primary key,
  "Code" text not null,
  "CompanyParentFlg" boolean default false not null,
  "CompanyFlg" boolean default false not null,
  "Description" text not null
);
ALTER TABLE "Feature" OWNER TO :db;

INSERT INTO "Feature" ( "Code", "CompanyParentFlg", "CompanyFlg", "Description" ) values ( 'TRANSAMERICA_COMMISSION_REPORT', true, true, 'features/transamerica_commission_report' );
INSERT INTO "Feature" ( "Code", "CompanyParentFlg", "CompanyFlg", "Description" ) values ( 'TRANSAMERICA_ACTUARIAL_REPORT', true, true, 'features/transamerica_actuarial_report' );
INSERT INTO "Feature" ( "Code", "CompanyParentFlg", "CompanyFlg", "Description" ) values ( 'TRANSAMERICA_ELIGIBILITY_REPORT', true, true, 'features/transamerica_eligibility_report' );


-- CompanyFeature
-- Table to hold the 'on/off' value of a feature as related to a company.
CREATE TABLE "CompanyFeature"
(
  "Id" serial not null constraint "CompanyFeature_pkey" primary key,
  "CompanyId" integer not null,
  "FeatureCode" text not null,
  "Enabled" boolean default false not null
);
ALTER TABLE "CompanyFeature" OWNER TO :db;


-- CompanyParentFeature
-- Table to hold the 'on/off' value of a feature as related to a company parent.
CREATE TABLE "CompanyParentFeature"
(
  "Id" serial not null constraint "CompanyParentFeature_pkey" primary key,
  "CompanyParentId" integer not null,
  "FeatureCode" text not null,
  "Enabled" boolean default false not null
);
ALTER TABLE "CompanyParentFeature" OWNER TO :db;

