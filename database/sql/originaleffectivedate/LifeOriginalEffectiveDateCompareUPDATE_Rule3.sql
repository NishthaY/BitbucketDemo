update "LifeOriginalEffectiveDateCompare" set
  "Code" = subquery."Code"
  , "Calculated-EffectiveDate" = subquery."CoverageStartDate"
  , "OldestLifePlanEffectiveDate" = subquery."CoverageStartDate"
  , "OldestLifePlanDiscoveryDate" = subquery."ImportDate"
  , "Description" = subquery."Description"
  , "OEDReset" = true
from
(
  select
    now."ImportDataId"
    , now."ImportDate"
    , 'UPDATE' as "Code"
    , "CompanyPlanType"."RetroRule"
    , oed."EffectiveDate"
    , now."CoverageStartDate"
    , before."CoverageStartDate" as "Before-CoverageStartDate"
    , oed."DiscoveryDate"
    , (coalesce(now."OldestLifePlanDiscoveryDate", oed."DiscoveryDate") + ( "CompanyPlanType"."RetroRule"::int * '1 month'::INTERVAL ))::date as "LockDate"
    , coalesce(now."Description", '') || 'Updating because the current EF date was discovered on or before the lock date and was originally sourced from the coverage start date. ' as "Description"
  from
    "LifeOriginalEffectiveDateCompare" now
    left join "LifeOriginalEffectiveDateCompare" before on ( before."CompanyId" = now."CompanyId" and before."ImportDate" = now."ImportDate" + interval '-1 month' and before."LifeId" = now."LifeId" and before."CarrierId" = now."CarrierId" and before."PlanTypeId" = now."PlanTypeId" and before."PlanId" = now."PlanId" and before."CoverageTierId" = now."CoverageTierId" )
    join "LifeOriginalEffectiveDate" oed on ( oed."LifeId" = now."LifeId" and oed."CarrierId" = now."CarrierId" and oed."PlanTypeId" = now."PlanTypeId" and oed."PlanId" = now."PlanId" and oed."CoverageTierId" = now."CoverageTierId" and oed."IsCoverageStartDate" = true	)
    join "CompanyPlanType" on ( "CompanyPlanType"."CarrierId" = now."CarrierId" and "CompanyPlanType"."Id" = now."PlanTypeId" )
    join "RetroData" r on ( r."CompanyId" = now."CompanyId" and r."ImportDate" = now."ImportDate" and r."LifeId" = now."LifeId" and r."CarrierId" = now."CarrierId" and r."PlanTypeId" = now."PlanTypeId" and r."PlanId" = now."PlanId" and r."CoverageTierId" = now."CoverageTierId")
  where
    now."CompanyId" = ?
    and now."ImportDate" = ?

    -- Consider items that are an UPDATE or EXISTING.
    and (now."Code" = 'UPDATE' OR now."Code" = 'EXISTING')

    -- The coverage start date is moving into the future compared to the previous coverage start date.
    and now."CoverageStartDate" > before."CoverageStartDate"

    -- The month we are importing is in range of the retro window.
    -- OldestLifePlanDiscoveryDate
    --   If you have this date, then every coverage tier assumes the oldest in the life plan.  Use the original discovery date.
    -- DiscoveryDate
    --   If you only have this date, then we allow the change window to be in play every time there is a coverage tier change.
    and now."ImportDate" <= (coalesce(now."OldestLifePlanDiscoveryDate", oed."DiscoveryDate") + ( "CompanyPlanType"."RetroRule"::int * '1 month'::INTERVAL ))::date

    -- The calculated effective date that we have in hand right now does not match the current lockbox value.
    -- If the Calculated-EffectiveDate matches the locked Effective date, then we are in a scenario where
    and now."CoverageStartDate" <> oed."EffectiveDate"

    -- If the RetroEngine reports this is just a RetroChange that effects the CSD
    -- then we can accept the CSD correction.  If there are other RetroChanges in play
    -- then this is more than a correction and we will not treat it as such.
    and r."AdjustmentType"  = 4

) as subquery
where
  "LifeOriginalEffectiveDateCompare"."ImportDataId" = subquery."ImportDataId"
