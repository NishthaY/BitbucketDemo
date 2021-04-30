insert into "RetroDataLifeEvent" ( "CompanyId", "ImportDate", "RetroDataId", "LifeEvent", "AutoSelected", "CoverageStartDate", "Before-CoverageStartDateList", "PlanId", "Before-PlanId", "CoverageTierId", "Before-CoverageTierIdList", "Volume", "Before-Volume","MonthlyCost", "Before-MonthlyCost", "CarrierId", "PlanTypeId" )
select
    "RetroData"."CompanyId"
	, "RetroData"."ImportDate"
	, "RetroData"."Id"  as "RetroDataId"
	, true as "LifeEvent"
	, true as "AutoSelected"
    , "RetroData"."CoverageStartDate" as "CoverageStartDate"
    , "RetroData"."Before-CoverageStartDate"::text as "Before-CoverageStartDateList"
    , "RetroData"."PlanId"
    , "RetroData"."Before-PlanId"
    , "RetroData"."CoverageTierId"
    , "RetroData"."Before-CoverageTierKey"::text as "Before-CoverageTierIdList"
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
	and "RetroData"."AdjustmentType" in ( 4, 5, 6 )                                                -- retro changed
	and "RetroData"."CoverageStartDate" > "RetroData"."Before-CoverageStartDate"                   -- new coverage start date more recent than previous.
	and "RetroData"."PlanId" = "RetroData"."Before-PlanId"                                         -- Plans have not changed
    AND extract(day from "RetroData"."CoverageStartDate") <> 1                                     -- not the first of the month.
    -- One of the following have changed.
	and (
		"RetroData"."CoverageTierKey" <> "RetroData"."Before-CoverageTierKey"
		or coalesce("RetroData"."Volume"::text, '') <> coalesce("RetroData"."Before-Volume", '')
		or coalesce("RetroData"."MonthlyCost"::text,'') <> coalesce("RetroData"."Before-MonthlyCost", '')
	)
