\set db advice2pay

-- Add the DEFAULT_CLARIFICATIONS feature.
insert into "Feature" ("Id", "Code", "CompanyParentFlg", "CompanyFlg", "Description") values ( 12, 'DEFAULT_CLARIFICATIONS', true, true, 'features/default_clarifications');

-- Grant Transamerica access to the new feature.
insert into "CompanyParentFeature" ( "CompanyParentId", "FeatureCode", "Enabled" )
select
    ( select "Id" from "CompanyParent" where "Name" = 'Transamerica' )
     , 'DEFAULT_CLARIFICATIONS'
     , false
WHERE EXISTS ( select 1 from "CompanyParent" where "Name" = 'Transamerica' );

-- Add a new column to the RetroDataLifeEvent table
alter table "RetroDataLifeEvent" add "DefaultType" text;

-- RetroDataLifeEventWarnings
-- Create a table to hold warnings specific to the retro data life event process.
create table "RetroDataLifeEventWarning"
(
    "Id" bigserial not null constraint "RetroDataLifeEventWarningsId" primary key,
    "CompanyId" integer not null,
    "ImportDate" date not null,
    "ImportDataId" integer not null,
    "IssueType" text not null,
    "Issue" text not null
);
create index retrodatalifeeventwarnings_id_idx
    on "RetroDataLifeEventWarning" ("Id");
create index retrodatalifeeventwarnings_companyid_importdate_idx
    on "RetroDataLifeEventWarning" ("CompanyId", "ImportDate");