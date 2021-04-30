\set db advice2pay

create table "PerformanceSource"
(
  "Id" serial not null
    constraint "PerformanceSource_pkey"
    primary key,
  "CompanyId" integer not null,
  "ImportDate" date not null,
  "DATA" text
)
;

create index performancesource_companyid_importdate_index
  on "PerformanceSource" ("CompanyId", "ImportDate")
;

create index performancesource_companyid_index
  on "PerformanceSource" ("CompanyId")
;


create table if not exists "PerformanceTargetA"
(
  "Id" serial not null
    constraint "PerformanceTargetA_pkey"
    primary key,
  "CompanyId" integer not null,
  "ImportDate" date not null,
  "SourceDataId" integer not null,
  "DATA" text
)
;

create index if not exists performancetargeta_companyid_importdate_sourcedataid_index
  on "PerformanceTargetA" ("CompanyId", "ImportDate", "SourceDataId")
;

create index if not exists performancetargeta_companyid_importdate_index
  on "PerformanceTargetA" ("CompanyId", "ImportDate")
;

create index if not exists performancetargets_companyid_index
  on "PerformanceTargetA" ("CompanyId")
;

