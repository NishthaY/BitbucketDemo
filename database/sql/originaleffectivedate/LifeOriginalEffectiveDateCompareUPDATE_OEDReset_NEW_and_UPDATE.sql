update "LifeOriginalEffectiveDateCompare" set
  "OEDReset" = true
  , "OldestLifePlanDiscoveryDate" = subquery."ImportDate"
  , "OldestLifePlanEffectiveDate" = subquery."CoverageStartDate"
  , "Description" = 'Calculated the effective date based on import data because this is a new item and there is a gap in coverage. ( NEW/UPDATE )'
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
      join "LifeOriginalEffectiveDateCompare" existing on
                                                         (
                                                           new."CompanyId" = existing."CompanyId"
                                                           and new."ImportDate" = existing."ImportDate"
                                                           and new."LifeId" = existing."LifeId"
                                                           and new."CarrierId" = existing."CarrierId"
                                                           and new."PlanTypeId" = existing."PlanTypeId"
                                                           and new."PlanId" = existing."PlanId"
                                                           and existing."Code" = 'UPDATE'
                                                           )
    where
      new."CompanyId" = ?
      and new."ImportDate" = ?
      and new."Code" = 'NEW'
      and ( new."OEDReset" is null OR new."OEDReset" <> true )

      -- Allow for a tiny gap of one day between lost date and start date.
      -- If it was the last day of the previous month, the intention is no gap.
      and existing."LostDate" < (new."CoverageStartDate" + INTERVAL '-1 day')

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
