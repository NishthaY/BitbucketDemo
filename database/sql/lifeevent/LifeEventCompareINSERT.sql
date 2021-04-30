insert into "LifeEventCompare" ( "CompanyId", "ImportDate", "LifeEvent", "PlanId", "CoverageTierId", "CoverageStartDate", "Before-CoverageStartDateList", "MonthlyCost", "Before-MonthlyCost", "Volume", "Before-Volume", "CarrierId", "PlanTypeId" )
select
	"CompanyId"
	, "ImportDate"
	, "LifeEvent"
	, "PlanId"
	, "CoverageTierId"
	, "CoverageStartDate"
	, "Before-CoverageStartDateList" as "Before-CoverageStartDateList"
	, "MonthlyCost"
	, "Before-MonthlyCost"
	, "Volume"
	, "Before-Volume"
	, "CarrierId"
	, "PlanTypeId"
from
	"RetroDataLifeEvent"
where
    1=1
	and "CompanyId" = ?
	and "ImportDate" = ?
    and "AutoSelected" = false
