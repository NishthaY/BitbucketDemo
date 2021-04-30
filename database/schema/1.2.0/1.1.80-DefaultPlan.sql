\set db advice2pay

-- FEATURE
-- Add the DEFAULT_PLAN feature.
INSERT INTO "Feature" ( "Id", "Code", "CompanyParentFlg", "CompanyFlg", "Description", "Targetable", "TargetType" ) values ( 11, 'DEFAULT_PLAN', true, true, 'features/default_plan', false, null );