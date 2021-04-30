update "LifeOriginalEffectiveDate" set
  "EffectiveDate" = subquery."EffectiveDate"
  , "DiscoveryDate" = subquery."DiscoveryDate"
  , "IsCoverageStartDate" = subquery."IsCoverageStartDate"
  , "LostDate" = subquery."LostDate"
from
(
  SELECT
    "LifeOriginalEffectiveDateCompare"."LifeId"
    , "LifeOriginalEffectiveDateCompare"."CarrierId"
    , "LifeOriginalEffectiveDateCompare"."PlanTypeId"
    , "LifeOriginalEffectiveDateCompare"."PlanId"
    , "LifeOriginalEffectiveDateCompare"."CoverageTierId"
    , "LifeOriginalEffectiveDateCompare"."Calculated-EffectiveDate" as "EffectiveDate"
    , "LifeOriginalEffectiveDateCompare"."ImportDate" as "DiscoveryDate"
    , "LifeOriginalEffectiveDateCompare"."IsCoverageStartDate"
    , "LifeOriginalEffectiveDateCompare"."LostDate"
  from
    "LifeOriginalEffectiveDateCompare"
  where
    "LifeOriginalEffectiveDateCompare"."CompanyId" = ?
    and "LifeOriginalEffectiveDateCompare"."ImportDate" = ?
    and "LifeOriginalEffectiveDateCompare"."Code" = 'RESTART'
) as subquery
where
  "LifeOriginalEffectiveDate"."LifeId" = subquery."LifeId"
  and "LifeOriginalEffectiveDate"."CarrierId" = subquery."CarrierId"
  and "LifeOriginalEffectiveDate"."PlanTypeId" = subquery."PlanTypeId"
  and "LifeOriginalEffectiveDate"."PlanId" = subquery."PlanId"
  and "LifeOriginalEffectiveDate"."CoverageTierId" = subquery."CoverageTierId"