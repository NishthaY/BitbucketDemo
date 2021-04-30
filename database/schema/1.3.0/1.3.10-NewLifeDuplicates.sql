\set db advice2pay

create table "ImportLifeWorker"
(
    "Id" serial not null constraint "ImportLifeWorker_pk" primary key,
    "CompanyId" integer not null,
    "ImportDate" date not null,
    "LifeKey" text not null,
    "TargetId" integer
);
create unique index importlifeworker_id_uindex on "ImportLifeWorker" ("Id");
create index importlifeworker_companyid_importdate_lifekey_index on "ImportLifeWorker" ("CompanyId", "ImportDate", "LifeKey");



create table "ImportLifeWarning"
(
    "Id" bigserial not null constraint "ImportLifeWarningId" primary key,
    "CompanyId" integer not null,
    "ImportDate" date not null,
    "LifeKey" text,
    "RecordCount" integer
);
create index importlifewarning_id_idx on "ImportLifeWarning" ("Id");
create index importlifewarning_companyid_importdate_index on "ImportLifeWarning" ("CompanyId", "ImportDate");
create index importlifewarning_companyid_lifekey_index on "ImportLifeWarning" ("CompanyId", "LifeKey");

