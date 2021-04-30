\set db advice2pay


-- Add a new property to our mapping columns.  For the default and the company override.
alter table "MappingColumns" add "NormalizationRegEx" text;
alter table "CompanyMappingColumn" add "NormalizationRegEx" text;

-- Features are now Targetable!
alter table "Feature" add "Targetable" BOOLEAN default FALSE not null;
alter table "Feature" add "TargetType" text;
alter table "CompanyParentFeature" add "Target" text;
alter table "CompanyFeature" add "Target" text;

-- Create a table to keep track of all possible target types for features.
create table "FeatureTargetType"
(
    "Code" text not null,
    "Description" text not null
);
insert into "FeatureTargetType" ( "Code", "Description") values ( 'mapping_column', 'Target an individual mapping column per feature.');

-- Add the COLUMN_NORMALIZATION_REGEX feature.
insert into "Feature" ("Id", "Code", "CompanyParentFlg", "CompanyFlg", "Description", "Targetable", "TargetType") values ( 8, 'COLUMN_NORMALIZATION_REGEX', true, true, 'features/column_normalization_regex', true, 'mapping_column');

-- Grant Transamerica access to the new targetable feature.
insert into "CompanyParentFeature" ( "CompanyParentId", "FeatureCode", "Enabled", "Target" )
select
    ( select "Id" from "CompanyParent" where "Name" = 'Transamerica' )
     , 'COLUMN_NORMALIZATION_REGEX'
     , false
     , 'plan'
WHERE EXISTS ( select 1 from "CompanyParent" where "Name" = 'Transamerica' );


