\set db advice2pay

create table "LifeOriginalEffectiveDateWarning"
(
  "Id" serial not null constraint "LifeOriginalEffectiveDateWarning_pkey" primary key,
  "CompanyId" integer not null,
  "ImportDate" date not null,
  "ImportDataId" integer,
  "Tag" text,
  "Issue" text,
  "Internal" boolean default false not null
);