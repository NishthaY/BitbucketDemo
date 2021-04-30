insert into "LifeOriginalEffectiveDateRollback" ( "CompanyId", "ImportDate", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId", "EffectiveDate", "DiscoveryDate","IsCoverageStartDate", "LostDate", "Code")
SELECT
  "LifeOriginalEffectiveDateCompare"."CompanyId"
  , "LifeOriginalEffectiveDateCompare"."ImportDate"
  , "LifeOriginalEffectiveDate"."LifeId"
  , "LifeOriginalEffectiveDate"."CarrierId"
  , "LifeOriginalEffectiveDate"."PlanTypeId"
  , "LifeOriginalEffectiveDate"."PlanId"
  , "LifeOriginalEffectiveDate"."CoverageTierId"
  , "LifeOriginalEffectiveDate"."EffectiveDate"
  , "LifeOriginalEffectiveDate"."DiscoveryDate"
  , "LifeOriginalEffectiveDate"."IsCoverageStartDate"
  , "LifeOriginalEffectiveDate"."LostDate"
  , 'UPDATE' as "Code"
from
  "LifeOriginalEffectiveDate"
  join "LifeOriginalEffectiveDateCompare" on (
    "LifeOriginalEffectiveDateCompare"."LifeId" = "LifeOriginalEffectiveDate"."LifeId"
    and "LifeOriginalEffectiveDateCompare"."CarrierId" = "LifeOriginalEffectiveDate"."CarrierId"
    and "LifeOriginalEffectiveDateCompare"."PlanTypeId" = "LifeOriginalEffectiveDate"."PlanTypeId"
    and "LifeOriginalEffectiveDateCompare"."PlanId" = "LifeOriginalEffectiveDate"."PlanId"
    and "LifeOriginalEffectiveDateCompare"."CoverageTierId" = "LifeOriginalEffectiveDate"."CoverageTierId"
)
where
  "LifeOriginalEffectiveDateCompare"."CompanyId" = ?
  and "LifeOriginalEffectiveDateCompare"."ImportDate" = ?
  and "LifeOriginalEffectiveDateCompare"."Code" = 'UPDATE'