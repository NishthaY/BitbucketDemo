insert into "CompanyCommissionDataCompare" ( "CompanyId", "ImportDate", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId", "OEDReset", "TierChanged", "VolumeChanged", "MonthlyCostChanged", "VolumeIncreased", "MonthlyCostIncreased", "CoverageStartDateChanged", "CoverageGapOffset")
select
    now."CompanyId"
     ,now."ImportDate"
     ,now."LifeId"
     ,now."CarrierId"
     ,now."PlanTypeId"
     ,now."PlanId"
     ,now."CoverageTierId"
     , now."OEDReset" as "OEDReset"
     , null as "TierChanged" -- We will set this after the inital insert.
     , (coalesce(before."Volume", 0) <> coalesce(now."Volume",0))                                                                                                                         as "VolumeChanged"
     , (coalesce(before."MonthlyCost", 0) <> coalesce(now."MonthlyCost",0))                                                                                                               as "MonthlyCostChanged"
     , case  when (coalesce(before."Volume", 0) <> coalesce(now."Volume",0)) AND (coalesce(now."Volume", 0) > coalesce(before."Volume", 0) ) then true else false end                     as "VolumeIncreased"
     , case  when (coalesce(before."MonthlyCost", 0) <> coalesce(now."MonthlyCost",0)) AND (coalesce(now."MonthlyCost", 0) > coalesce(before."MonthlyCost", 0) ) then true else false end as "MonthlyCostIncreased"
     , case
           WHEN before."CoverageStartDate" is null then false
           WHEN now."CoverageStartDate" <> before."CoverageStartDate" THEN true
           ELSE false
    END as "CoverageStartDateChanged"
    , ? as "CoverageOffsetGap"
from
    "CompanyCommissionData" now
    left join "CompanyCommissionData" before on (before."CompanyId" = now."CompanyId" and before."ImportDate" = ? and  before."LifeId" = now."LifeId" and before."CarrierId" = now."CarrierId" and before."PlanTypeId" = now."PlanTypeId" and before."PlanId" = now."PlanId")  --  and before."CoverageTierId" = now."CoverageTierId"
where
    now."CompanyId" = ?
    and now."ImportDate" = ?
    and now."LifeId" = ?
    and now."CarrierId" = ?
    and now."PlanTypeId" = ?
    and now."PlanId" = ?
    and now."CoverageTierId" = ?

-- NOTE: If you are changing this query, don't forget there is a bulk query too.
