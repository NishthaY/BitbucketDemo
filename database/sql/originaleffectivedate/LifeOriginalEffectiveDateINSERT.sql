insert into "LifeOriginalEffectiveDate" ( "LifeId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId", "EffectiveDate", "DiscoveryDate", "IsCoverageStartDate" )
SELECT
  "LifeOriginalEffectiveDateCompare"."LifeId"
  , "LifeOriginalEffectiveDateCompare"."CarrierId"
  , "LifeOriginalEffectiveDateCompare"."PlanTypeId"
  , "LifeOriginalEffectiveDateCompare"."PlanId"
  , "LifeOriginalEffectiveDateCompare"."CoverageTierId"
  , "LifeOriginalEffectiveDateCompare"."Calculated-EffectiveDate" as "EffectiveDate"
  , "LifeOriginalEffectiveDateCompare"."ImportDate" as "DiscoveryDate"
  , "LifeOriginalEffectiveDateCompare"."IsCoverageStartDate"
from
  "LifeOriginalEffectiveDateCompare"
where
  "LifeOriginalEffectiveDateCompare"."CompanyId" = ?
  and "LifeOriginalEffectiveDateCompare"."ImportDate" = ?
  and "LifeOriginalEffectiveDateCompare"."Code" = 'NEW'