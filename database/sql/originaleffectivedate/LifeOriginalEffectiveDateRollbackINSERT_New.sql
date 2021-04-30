insert into "LifeOriginalEffectiveDateRollback" ( "CompanyId", "ImportDate", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId", "LostDate", "Code")
SELECT
  "LifeOriginalEffectiveDateCompare"."CompanyId"
  , "LifeOriginalEffectiveDateCompare"."ImportDate"
  , "LifeOriginalEffectiveDateCompare"."LifeId"
  , "LifeOriginalEffectiveDateCompare"."CarrierId"
  , "LifeOriginalEffectiveDateCompare"."PlanTypeId"
  , "LifeOriginalEffectiveDateCompare"."PlanId"
  , "LifeOriginalEffectiveDateCompare"."CoverageTierId"
  , "LifeOriginalEffectiveDateCompare"."LostDate"
  , 'DELETE' as "Code"
from
  "LifeOriginalEffectiveDateCompare"
where
  "LifeOriginalEffectiveDateCompare"."CompanyId" = ?
  and "LifeOriginalEffectiveDateCompare"."ImportDate" = ?
  and "LifeOriginalEffectiveDateCompare"."Code" = 'NEW'