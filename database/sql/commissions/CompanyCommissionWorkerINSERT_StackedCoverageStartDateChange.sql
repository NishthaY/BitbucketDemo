insert into "CompanyCommissionWorker" ( "CompanyId", "ImportDate", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "CommissionEffectiveDate", "MonthlyCost")
select
  cc."CompanyId"
  , cc."ImportDate"
  , cc."LifeId"
  , cc."CarrierId"
  , cc."PlanTypeId"
  , cc."PlanId"
  , d."CoverageStartDate"
  , d."MonthlyCost"
from "CompanyCommission" cc
  join "CompanyCommissionDataCompare" compare on (compare."CompanyId"=cc."CompanyId" and compare."ImportDate"=cc."ImportDate" and compare."LifeId"=cc."LifeId" and compare."CarrierId"=cc."CarrierId" and compare."PlanTypeId"=cc."PlanTypeId" and compare."PlanId"=cc."PlanId")
  JOIN "CompanyCommissionData" as d on (d."CompanyId"=compare."CompanyId" and d."ImportDate"=compare."ImportDate" and d."LifeId"=compare."LifeId" and d."CarrierId"=compare."CarrierId" and d."PlanTypeId"=compare."PlanTypeId" and d."PlanId"=compare."PlanId" and d."CoverageTierId"=compare."CoverageTierId" )
WHERE
  cc."CompanyId" = ?
  and cc."ImportDate" = ?
  and compare."CompanyId" = cc."CompanyId"
  and compare."ImportDate" = cc."ImportDate"
  and compare."LifeId" = cc."LifeId"
  and compare."CarrierId" = cc."CarrierId"
  and compare."PlanTypeId" = cc."PlanTypeId"
  and compare."PlanId" = cc."PlanId"

  -- Do not allow CSD changes on reset records.  Those records are controlled by the OED table and
  -- we will update them on a different query, if needed.
  --and cc."ResetRecord" = false

  -- If the coverage start date changed and no other change vectors have changed, this is a correction.
  and (
    compare."CoverageStartDateChanged" = true
    and coalesce(compare."TierChanged", false) = false
    and coalesce(compare."VolumeChanged", false) = false
    and coalesce(compare."MonthlyCostChanged", false) = false
  )

  -- There could be many stacks on this record.  We only want to update the CED on the record
  -- that matched the date we had last month.
  and cc."CommissionEffectiveDate" = d."Before-CoverageStartDate"

  -- Ignore warning records.
  and coalesce(compare."Code", '') <> 'WARNING'