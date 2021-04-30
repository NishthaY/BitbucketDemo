update "LifeOriginalEffectiveDateCompare" set
  "OEDReset" = true
  , "OldestLifePlanDiscoveryDate" = subquery."ImportDate"
  , "OldestLifePlanEffectiveDate" = subquery."CoverageStartDate"
  , "Calculated-EffectiveDate" = subquery."CoverageStartDate"
  , "Description" = 'Calculated the effective date based on import data because this is a new item and there is a gap in coverage. ( NEW/MISSING )'
from
  (
    select
      new."CompanyId"
      , new."ImportDate"
      , new."LifeId"
      , new."CarrierId"
      , new."PlanTypeId"
      , new."PlanId"
      , new."CoverageTierId"
      , new."CoverageStartDate"
    from
      "LifeOriginalEffectiveDateCompare" new
      join "LifeOriginalEffectiveDateCompare" missing on
                                                         (
                                                           new."CompanyId" = missing."CompanyId"
                                                           and new."ImportDate" = missing."ImportDate"
                                                           and new."LifeId" = missing."LifeId"
                                                           and new."CarrierId" = missing."CarrierId"
                                                           and new."PlanTypeId" = missing."PlanTypeId"
                                                           and new."PlanId" = missing."PlanId"
                                                           and missing."Code" = 'MISSING'
                                                           and missing."LostDate" is not null
                                                           )
    where
      new."CompanyId" = ?
      and new."ImportDate" = ?
      and new."Code" = 'NEW'
      and ( new."OEDReset" is null OR new."OEDReset" <> true )
      and missing."LostDate" < new."CoverageStartDate"

      -- Allow for a tiny gap of one day between lost date and start date.
      -- If it was the last day of the previous month, the intention is no gap.
      and missing."LostDate" < (new."CoverageStartDate" + INTERVAL '-1 day')

  ) as subquery
where
  "LifeOriginalEffectiveDateCompare"."CompanyId" = subquery."CompanyId"
  and "LifeOriginalEffectiveDateCompare"."ImportDate" = subquery."ImportDate"
  and "LifeOriginalEffectiveDateCompare"."LifeId" = subquery."LifeId"
  and "LifeOriginalEffectiveDateCompare"."CarrierId" = subquery."CarrierId"
  and "LifeOriginalEffectiveDateCompare"."PlanTypeId" = subquery."PlanTypeId"
  and "LifeOriginalEffectiveDateCompare"."PlanId" = subquery."PlanId"
  and "LifeOriginalEffectiveDateCompare"."CoverageTierId" = subquery."CoverageTierId"
  and "LifeOriginalEffectiveDateCompare"."Code" = 'NEW'