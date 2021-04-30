update "LifeOriginalEffectiveDateCompare" set
  "OEDReset" = true
  , "OldestLifePlanDiscoveryDate" = subquery."ImportDate"
  , "OldestLifePlanEffectiveDate" = subquery."CoverageStartDate"
  , "Calculated-EffectiveDate" = subquery."CoverageStartDate"
from
  (
    select
      restart."CompanyId"
      , restart."ImportDate"
      , restart."LifeId"
      , restart."CarrierId"
      , restart."PlanTypeId"
      , restart."PlanId"
      , restart."CoverageTierId"
      , restart."CoverageStartDate"
    from
      "LifeOriginalEffectiveDateCompare" restart
    where
      restart."CompanyId" = ?
      and restart."ImportDate" = ?
      and restart."Code" = 'RESTART'
      and ( restart."OEDReset" is null OR restart."OEDReset" <> true )
  ) as subquery
where
  "LifeOriginalEffectiveDateCompare"."CompanyId" = subquery."CompanyId"
  and "LifeOriginalEffectiveDateCompare"."ImportDate" = subquery."ImportDate"
  and "LifeOriginalEffectiveDateCompare"."LifeId" = subquery."LifeId"
  and "LifeOriginalEffectiveDateCompare"."CarrierId" = subquery."CarrierId"
  and "LifeOriginalEffectiveDateCompare"."PlanTypeId" = subquery."PlanTypeId"
  and "LifeOriginalEffectiveDateCompare"."PlanId" = subquery."PlanId"
  and "LifeOriginalEffectiveDateCompare"."CoverageTierId" = subquery."CoverageTierId"
  and "LifeOriginalEffectiveDateCompare"."Code" = 'RESTART'