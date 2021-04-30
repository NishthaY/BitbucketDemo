\set db advice2pay

create table "ImportLife"
(
  "ImportDataId" integer not null
    constraint importlife_pkey
    primary key,
  "CompanyId" integer not null,
  "ImportDate" date not null,
  "LifeKey" text
)
;

create index "ImportLife_CompanyId_ImportDate_index"
  on "ImportLife" ("CompanyId", "ImportDate")
;

ALTER TABLE "ImportLife" OWNER TO :db;
