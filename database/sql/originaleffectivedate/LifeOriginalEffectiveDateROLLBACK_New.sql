delete from "LifeOriginalEffectiveDate"
using "LifeOriginalEffectiveDateRollback"
where
  "LifeOriginalEffectiveDateRollback"."LifeId" = "LifeOriginalEffectiveDate"."LifeId"
  and "LifeOriginalEffectiveDateRollback"."CarrierId" = "LifeOriginalEffectiveDate"."CarrierId"
  and "LifeOriginalEffectiveDateRollback"."PlanTypeId" = "LifeOriginalEffectiveDate"."PlanTypeId"
  and "LifeOriginalEffectiveDateRollback"."PlanId" = "LifeOriginalEffectiveDate"."PlanId"
  and "LifeOriginalEffectiveDateRollback"."CoverageTierId" = "LifeOriginalEffectiveDate"."CoverageTierId"
  and "LifeOriginalEffectiveDateRollback"."CompanyId" = ?
  and "LifeOriginalEffectiveDateRollback"."ImportDate" = ?
  and "LifeOriginalEffectiveDateRollback"."Code" = 'DELETE'