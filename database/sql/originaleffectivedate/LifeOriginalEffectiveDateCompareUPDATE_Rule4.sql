-- HEY!!!!! HAVE YOU READ THIS COMMENT
--
-- Updating the inner select?  Make sure you change the corresponding LOG query too.
-- We need to log what we update here.
--
-- HEY!!!!! HAVE YOU READ THIS COMMENT

update "LifeOriginalEffectiveDateCompare" set
  "Code" = subquery."Code"
  , "Description" = subquery."Description"
  , "IsCoverageStartDate" = true
  , "OldestLifePlanEffectiveDate" = subquery."Calculated-EffectiveDate"
from
(
  select
    "LifeOriginalEffectiveDateCompare"."ImportDataId"
    , 'UPDATE' as "Code"
    , "CompanyPlanType"."RetroRule"
    , "LifeOriginalEffectiveDate"."EffectiveDate"
    , "LifeOriginalEffectiveDateCompare"."Calculated-EffectiveDate" as "Calculated-EffectiveDate"
    , "LifeOriginalEffectiveDate"."DiscoveryDate"
    , ("LifeOriginalEffectiveDate"."DiscoveryDate" + ( "CompanyPlanType"."RetroRule"::int * '1 month'::INTERVAL ))::date as "LockDate"
    , coalesce("LifeOriginalEffectiveDateCompare"."Description", '') || 'Updating because the coverage start date provided is before the current EF date. ' as "Description"
  from
    "LifeOriginalEffectiveDateCompare"
    join "CompanyPlanType" on ( "CompanyPlanType"."CarrierId" = "LifeOriginalEffectiveDateCompare"."CarrierId" and "CompanyPlanType"."Id" = "LifeOriginalEffectiveDateCompare"."PlanTypeId" )
    join "LifeOriginalEffectiveDate" on ( "LifeOriginalEffectiveDate"."LifeId" = "LifeOriginalEffectiveDateCompare"."LifeId" and "LifeOriginalEffectiveDate"."CarrierId" = "LifeOriginalEffectiveDateCompare"."CarrierId" and "LifeOriginalEffectiveDate"."PlanTypeId" = "LifeOriginalEffectiveDateCompare"."PlanTypeId" and "LifeOriginalEffectiveDate"."PlanId" = "LifeOriginalEffectiveDateCompare"."PlanId" and "LifeOriginalEffectiveDate"."CoverageTierId" = "LifeOriginalEffectiveDateCompare"."CoverageTierId")
  where
    "LifeOriginalEffectiveDateCompare"."CompanyId" = ?
    and "LifeOriginalEffectiveDateCompare"."ImportDate" = ?
    and ("LifeOriginalEffectiveDateCompare"."Code" = 'UPDATE' OR "LifeOriginalEffectiveDateCompare"."Code" = 'EXISTING')
    and "LifeOriginalEffectiveDateCompare"."Calculated-EffectiveDate" < "LifeOriginalEffectiveDate"."EffectiveDate"
) as subquery
where
  "LifeOriginalEffectiveDateCompare"."ImportDataId" = subquery."ImportDataId"
