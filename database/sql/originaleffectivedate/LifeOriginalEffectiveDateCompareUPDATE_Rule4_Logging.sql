insert into "LifeOriginalEffectiveDateWarning" ("CompanyId", "ImportDate", "ImportDataId", "Tag", "Issue" )
select
  "LifeOriginalEffectiveDateCompare"."CompanyId"
  , "LifeOriginalEffectiveDateCompare"."ImportDate"
  , "LifeOriginalEffectiveDateCompare"."ImportDataId"
  , 'EarlierThanLockbox' as "Tag"
  , 'Found a coverage start date earlier than the current effective date.  Changing the effective date and commission data to reflect the earlier effective date.' as "Description"
from
  "LifeOriginalEffectiveDateCompare"
  join "CompanyPlanType" on ( "CompanyPlanType"."CarrierId" = "LifeOriginalEffectiveDateCompare"."CarrierId" and "CompanyPlanType"."Id" = "LifeOriginalEffectiveDateCompare"."PlanTypeId" )
  join "LifeOriginalEffectiveDate" on ( "LifeOriginalEffectiveDate"."LifeId" = "LifeOriginalEffectiveDateCompare"."LifeId" and "LifeOriginalEffectiveDate"."CarrierId" = "LifeOriginalEffectiveDateCompare"."CarrierId" and "LifeOriginalEffectiveDate"."PlanTypeId" = "LifeOriginalEffectiveDateCompare"."PlanTypeId" and "LifeOriginalEffectiveDate"."PlanId" = "LifeOriginalEffectiveDateCompare"."PlanId" and "LifeOriginalEffectiveDate"."CoverageTierId" = "LifeOriginalEffectiveDateCompare"."CoverageTierId")
where
  "LifeOriginalEffectiveDateCompare"."CompanyId" = ?
  and "LifeOriginalEffectiveDateCompare"."ImportDate" = ?
  and ("LifeOriginalEffectiveDateCompare"."Code" = 'UPDATE' OR "LifeOriginalEffectiveDateCompare"."Code" = 'EXISTING')
  and "LifeOriginalEffectiveDateCompare"."Calculated-EffectiveDate" < "LifeOriginalEffectiveDate"."EffectiveDate"