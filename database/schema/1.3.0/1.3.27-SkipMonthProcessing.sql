\set db advice2pay


-- Create a table to hold when a company skips a month by using the previous
-- months data file.
create table "SkipMonthProcessing"
(
    "Id" serial not null constraint """SkipMonthProcessing""_pk" primary key,
    "CompanyId" integer not null,
    "ImportDate" date not null
);
create unique index """SkipMonthProcessing""_""Id""_uindex" on "SkipMonthProcessing" ("Id");

