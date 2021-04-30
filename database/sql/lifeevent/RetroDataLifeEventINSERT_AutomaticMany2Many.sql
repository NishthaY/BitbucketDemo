insert into "RetroDataLifeEvent" ( "CompanyId", "ImportDate", "RetroDataId", "LifeEvent", "AutoSelected", "CoverageStartDate", "Before-CoverageStartDateList", "PlanId", "Before-PlanId",  "CoverageTierId", "Before-CoverageTierIdList", "Volume", "Before-Volume","MonthlyCost", "Before-MonthlyCost", "CarrierId", "PlanTypeId" )
select
    "RetroData"."CompanyId"
    , "RetroData"."ImportDate"
    , "RetroData"."Id"  as "RetroDataId"
    , null as "LifeEvent"
    , false as "AutoSelected"
    , "RetroData"."CoverageStartDate" as "CoverageStartDate"
    , "RetroData"."Before-CoverageStartDateList" as "Before-CoverageStartDateList"
    , "RetroData"."PlanId"
    , "RetroData"."Before-PlanId"
    , "RetroData"."CoverageTierId"
    , "RetroData"."Before-CoverageTierKey" as "Before-CoverageTierIdList"
    , "RetroData"."Volume"
    , "RetroData"."Before-Volume"
    , "RetroData"."MonthlyCost"
    , "RetroData"."Before-MonthlyCost"
    , "RetroData"."CarrierId"
    , "RetroData"."PlanTypeId"
from
	"RetroData"
where
	"RetroData"."CompanyId" = ?
	and "RetroData"."ImportDate" = ?
	and "RetroData"."AdjustmentType" in ( 4, 5, 6 )
	and "RetroData"."CoverageTierKey" <> "RetroData"."Before-CoverageTierKey"
	and "RetroData"."Before-CoverageStartDate" is null
    and "RetroData"."PlanId" = "RetroData"."Before-PlanId"      -- Plans have not changed
    and extract(day from "RetroData"."CoverageStartDate") <> 1  -- not the first of the month.
