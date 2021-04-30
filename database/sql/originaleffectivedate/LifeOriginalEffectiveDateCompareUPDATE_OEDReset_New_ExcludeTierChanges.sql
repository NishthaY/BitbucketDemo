-- If we have something that is NEW this month that has no records in the
-- OED table, then this is an OEDReset.
update "LifeOriginalEffectiveDateCompare" set
  "OEDReset" = true
  , "OldestLifePlanDiscoveryDate" = subquery."ImportDate"
  , "OldestLifePlanEffectiveDate" = subquery."CoverageStartDate"
  , "Calculated-EffectiveDate" = subquery."CoverageStartDate"
  , "Description" = 'Calculated the effective date based on import data because this is a new item. ( BRAND NEW )'
from
  (
    SELECT
      new."CompanyId"
      , new."ImportDate"
      , new."LifeId"
      , new."CarrierId"
      , new."PlanTypeId"
      , new."PlanId"

      -- If this is the starting month, we must keep the value that is in the
      -- Calculated-Effective date.  This will ensure we get the OED value if one was
      -- provided.  If it is not the starting month, then we can use the CSD value and
      -- pickup tier changes after the initial load.
      , case when new."ImportDate" = ? then new."Calculated-EffectiveDate" else new."CoverageStartDate" end as "CoverageStartDate"
    
    FROM
      "LifeOriginalEffectiveDateCompare" new
      left JOIN "LifeOriginalEffectiveDate" oed ON ( oed."LifeId" = new."LifeId" AND oed."CarrierId" = new."CarrierId" AND oed."PlanTypeId" = new."PlanTypeId" AND oed."PlanId" = new."PlanId")
    WHERE
      new."CompanyId" = ?
      AND new."ImportDate" = ?
      AND new."Code" = 'NEW'
      AND oed."EffectiveDate" IS NULL
  ) as subquery
where
  "LifeOriginalEffectiveDateCompare"."CompanyId" = subquery."CompanyId"
  and "LifeOriginalEffectiveDateCompare"."ImportDate" = subquery."ImportDate"
  and "LifeOriginalEffectiveDateCompare"."LifeId" = subquery."LifeId"
  and "LifeOriginalEffectiveDateCompare"."CarrierId" = subquery."CarrierId"
  and "LifeOriginalEffectiveDateCompare"."PlanTypeId" = subquery."PlanTypeId"
  and "LifeOriginalEffectiveDateCompare"."PlanId" = subquery."PlanId"
  and "LifeOriginalEffectiveDateCompare"."Code" = 'NEW'