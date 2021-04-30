update "LifeOriginalEffectiveDate" set
  "EffectiveDate" = subquery."EffectiveDate"
  , "DiscoveryDate" = subquery."DiscoveryDate"
  , "IsCoverageStartDate" = subquery."IsCoverageStartDate"
  , "LostDate" = subquery."LostDate"
from
(
  SELECT
    "LifeOriginalEffectiveDateRollback"."LifeId",
    "LifeOriginalEffectiveDateRollback"."CarrierId",
    "LifeOriginalEffectiveDateRollback"."PlanTypeId",
    "LifeOriginalEffectiveDateRollback"."PlanId",
    "LifeOriginalEffectiveDateRollback"."CoverageTierId",
    "LifeOriginalEffectiveDateRollback"."EffectiveDate",
    "LifeOriginalEffectiveDateRollback"."DiscoveryDate",
    "LifeOriginalEffectiveDateRollback"."IsCoverageStartDate",
    "LifeOriginalEffectiveDateRollback"."LostDate"
  FROM
    "LifeOriginalEffectiveDateRollback"
  WHERE
    "LifeOriginalEffectiveDateRollback"."CompanyId" = ?
    AND "LifeOriginalEffectiveDateRollback"."ImportDate" = ?
    AND "LifeOriginalEffectiveDateRollback"."Code" = 'UPDATE'
) as subquery
where
  "LifeOriginalEffectiveDate"."LifeId" = subquery."LifeId"
  and "LifeOriginalEffectiveDate"."CarrierId" = subquery."CarrierId"
  and "LifeOriginalEffectiveDate"."PlanTypeId" = subquery."PlanTypeId"
  and "LifeOriginalEffectiveDate"."PlanId" = subquery."PlanId"
  and "LifeOriginalEffectiveDate"."CoverageTierId" = subquery."CoverageTierId"
