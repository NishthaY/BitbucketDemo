\set db advice2pay

-- Add the DEFAULT_CARRIER feature.
insert into "Feature" ("Id", "Code", "CompanyParentFlg", "CompanyFlg", "Description") values ( 9, 'DEFAULT_CARRIER', true, true, 'features/default_carrier');

-- Grant Transamerica access to the new feature.
insert into "CompanyParentFeature" ( "CompanyParentId", "FeatureCode", "Enabled" )
select
    ( select "Id" from "CompanyParent" where "Name" = 'Transamerica' )
     , 'DEFAULT_CARRIER'
     , false
WHERE EXISTS ( select 1 from "CompanyParent" where "Name" = 'Transamerica' );

-- Grant Transamerica access to the new DEFAULT_CARRIER feature.
insert into "CompanyFeature" ( "CompanyId", "FeatureCode", "Enabled" )
select
    c."Id" as "CompanyId"
     , 'DEFAULT_CARRIER' as "FeatureCode"
     , false as "Enabled"
from
    "Company" c
        join "CompanyParentCompanyRelationship" r on ( r."CompanyId" = c."Id" )
where
        r."CompanyParentId" = ( select "Id" from "CompanyParent" where "Name" = 'Transamerica' );


-- AgeBandType
-- This table will hold "codes" for each of the different age band types that you can default.
create table "AgeBandType"
(
    "Id" integer not null constraint "AgeBandType_pk" primary key,
    "Code" text not null,
    "Description" text not null
);
insert into "AgeBandType" ( "Id", "Code", "Description" ) values ( 1, '5-YEAR', '5 Year Age Bands' );
insert into "AgeBandType" ( "Id", "Code", "Description" ) values ( 2, '10-YEAR', '10 Year Age Bands' );


-- AgeBandCarrier
-- Specific Age Bands to be used for different carriers.
create table "AgeBandCarrierDefault"
(
    "Id" bigserial not null constraint "AgeBandCarrierDefaultId" primary key,
    "CarrierCode" text not null,
    "AgeBandStart" integer not null,
    "AgeBandEnd" integer not null,
    "AgeBandTypeCode" text not null
);
insert into "AgeBandCarrierDefault" ( "CarrierCode", "AgeBandStart", "AgeBandEnd", "AgeBandTypeCode" ) values ( 'TRANSAMERICA', 0, 17, '5-YEAR' );
insert into "AgeBandCarrierDefault" ( "CarrierCode", "AgeBandStart", "AgeBandEnd", "AgeBandTypeCode" ) values ( 'TRANSAMERICA', 18, 24, '5-YEAR' );
insert into "AgeBandCarrierDefault" ( "CarrierCode", "AgeBandStart", "AgeBandEnd", "AgeBandTypeCode" ) values ( 'TRANSAMERICA', 25, 29, '5-YEAR' );
insert into "AgeBandCarrierDefault" ( "CarrierCode", "AgeBandStart", "AgeBandEnd", "AgeBandTypeCode" ) values ( 'TRANSAMERICA', 30, 34, '5-YEAR' );
insert into "AgeBandCarrierDefault" ( "CarrierCode", "AgeBandStart", "AgeBandEnd", "AgeBandTypeCode" ) values ( 'TRANSAMERICA', 35, 39, '5-YEAR' );
insert into "AgeBandCarrierDefault" ( "CarrierCode", "AgeBandStart", "AgeBandEnd", "AgeBandTypeCode" ) values ( 'TRANSAMERICA', 40, 44, '5-YEAR' );
insert into "AgeBandCarrierDefault" ( "CarrierCode", "AgeBandStart", "AgeBandEnd", "AgeBandTypeCode" ) values ( 'TRANSAMERICA', 45, 49, '5-YEAR' );
insert into "AgeBandCarrierDefault" ( "CarrierCode", "AgeBandStart", "AgeBandEnd", "AgeBandTypeCode" ) values ( 'TRANSAMERICA', 50, 54, '5-YEAR' );
insert into "AgeBandCarrierDefault" ( "CarrierCode", "AgeBandStart", "AgeBandEnd", "AgeBandTypeCode" ) values ( 'TRANSAMERICA', 55, 59, '5-YEAR' );
insert into "AgeBandCarrierDefault" ( "CarrierCode", "AgeBandStart", "AgeBandEnd", "AgeBandTypeCode" ) values ( 'TRANSAMERICA', 60, 64, '5-YEAR' );
insert into "AgeBandCarrierDefault" ( "CarrierCode", "AgeBandStart", "AgeBandEnd", "AgeBandTypeCode" ) values ( 'TRANSAMERICA', 65, 1000, '5-YEAR' );
insert into "AgeBandCarrierDefault" ( "CarrierCode", "AgeBandStart", "AgeBandEnd", "AgeBandTypeCode" ) values ( 'TRANSAMERICA', 0, 17, '10-YEAR' );
insert into "AgeBandCarrierDefault" ( "CarrierCode", "AgeBandStart", "AgeBandEnd", "AgeBandTypeCode" ) values ( 'TRANSAMERICA', 18, 29, '10-YEAR' );
insert into "AgeBandCarrierDefault" ( "CarrierCode", "AgeBandStart", "AgeBandEnd", "AgeBandTypeCode" ) values ( 'TRANSAMERICA', 30, 39, '10-YEAR' );
insert into "AgeBandCarrierDefault" ( "CarrierCode", "AgeBandStart", "AgeBandEnd", "AgeBandTypeCode" ) values ( 'TRANSAMERICA', 40, 49, '10-YEAR' );
insert into "AgeBandCarrierDefault" ( "CarrierCode", "AgeBandStart", "AgeBandEnd", "AgeBandTypeCode" ) values ( 'TRANSAMERICA', 50, 59, '10-YEAR' );
insert into "AgeBandCarrierDefault" ( "CarrierCode", "AgeBandStart", "AgeBandEnd", "AgeBandTypeCode" ) values ( 'TRANSAMERICA', 60, 64, '10-YEAR' );
insert into "AgeBandCarrierDefault" ( "CarrierCode", "AgeBandStart", "AgeBandEnd", "AgeBandTypeCode" ) values ( 'TRANSAMERICA', 65, 1000, '10-YEAR' );


-- AgeBandDefault
-- This table holds the A2P default age bands for each type to be used when
-- we don't have specific carrier preferred bands.
create table "AgeBandDefault"
(
    "Id" bigserial not null constraint "AgeBandDefaultId" primary key,
    "AgeBandStart" integer not null,
    "AgeBandEnd" integer not null,
    "AgeBandTypeCode" text not null
);
insert into "AgeBandDefault" ( "AgeBandStart", "AgeBandEnd", "AgeBandTypeCode" ) values ( 0, 19, '5-YEAR' );
insert into "AgeBandDefault" ( "AgeBandStart", "AgeBandEnd", "AgeBandTypeCode" ) values ( 20, 24, '5-YEAR' );
insert into "AgeBandDefault" ( "AgeBandStart", "AgeBandEnd", "AgeBandTypeCode" ) values ( 25, 29, '5-YEAR' );
insert into "AgeBandDefault" ( "AgeBandStart", "AgeBandEnd", "AgeBandTypeCode" ) values ( 30, 34, '5-YEAR' );
insert into "AgeBandDefault" ( "AgeBandStart", "AgeBandEnd", "AgeBandTypeCode" ) values ( 35, 39, '5-YEAR' );
insert into "AgeBandDefault" ( "AgeBandStart", "AgeBandEnd", "AgeBandTypeCode" ) values ( 40, 44, '5-YEAR' );
insert into "AgeBandDefault" ( "AgeBandStart", "AgeBandEnd", "AgeBandTypeCode" ) values ( 45, 49, '5-YEAR' );
insert into "AgeBandDefault" ( "AgeBandStart", "AgeBandEnd", "AgeBandTypeCode" ) values ( 50, 54, '5-YEAR' );
insert into "AgeBandDefault" ( "AgeBandStart", "AgeBandEnd", "AgeBandTypeCode" ) values ( 55, 59, '5-YEAR' );
insert into "AgeBandDefault" ( "AgeBandStart", "AgeBandEnd", "AgeBandTypeCode" ) values ( 60, 64, '5-YEAR' );
insert into "AgeBandDefault" ( "AgeBandStart", "AgeBandEnd", "AgeBandTypeCode" ) values ( 65, 69, '5-YEAR' );
insert into "AgeBandDefault" ( "AgeBandStart", "AgeBandEnd", "AgeBandTypeCode" ) values ( 70, 1000, '5-YEAR' );
insert into "AgeBandDefault" ( "AgeBandStart", "AgeBandEnd", "AgeBandTypeCode" ) values ( 0, 19, '10-YEAR' );
insert into "AgeBandDefault" ( "AgeBandStart", "AgeBandEnd", "AgeBandTypeCode" ) values ( 20, 29, '10-YEAR' );
insert into "AgeBandDefault" ( "AgeBandStart", "AgeBandEnd", "AgeBandTypeCode" ) values ( 30, 39, '10-YEAR' );
insert into "AgeBandDefault" ( "AgeBandStart", "AgeBandEnd", "AgeBandTypeCode" ) values ( 40, 49, '10-YEAR' );
insert into "AgeBandDefault" ( "AgeBandStart", "AgeBandEnd", "AgeBandTypeCode" ) values ( 50, 59, '10-YEAR' );
insert into "AgeBandDefault" ( "AgeBandStart", "AgeBandEnd", "AgeBandTypeCode" ) values ( 60, 69, '10-YEAR' );
insert into "AgeBandDefault" ( "AgeBandStart", "AgeBandEnd", "AgeBandTypeCode" ) values ( 70, 1000, '10-YEAR' );

