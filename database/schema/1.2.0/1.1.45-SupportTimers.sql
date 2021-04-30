\set db advice2pay

create table "SupportTimer"
(
    "Id" serial not null constraint "SupportTimer_pk" primary key,
    "CompanyId" integer not null,
    "ImportDate" date not null,
    "Tag" text not null,
    "Start" timestamp with time zone,
    "End" timestamp with time zone,
    "Estimated" boolean default false not null
);
create index supporttimer_companyid_importdate_index on "SupportTimer" ("CompanyId", "ImportDate");

