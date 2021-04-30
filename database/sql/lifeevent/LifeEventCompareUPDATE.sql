update "LifeEventCompare" set
    "LifeEvent" = subquery."NewLifeEvent"
from (
	select
		r."LifeEvent" as "NewLifeEvent"
		, l."Id" as "LifeEventCompareId"
	from
		"LifeEventCompare" l
		join "RetroDataLifeEvent" r on (
			r."CompanyId" = l."CompanyId"
			and r."ImportDate" = l."ImportDate"
			and r."PlanId" = l."PlanId"
			and r."CoverageTierId" = l."CoverageTierId"
			and r."CoverageStartDate" = l."CoverageStartDate"
			and r."Before-CoverageStartDateList" = l."Before-CoverageStartDateList"
			and r."MonthlyCost" = l."MonthlyCost"
			and r."Before-MonthlyCost" = l."Before-MonthlyCost"
			and r."Volume" = l."Volume"
			and r."Before-Volume" = l."Before-Volume"
			and r."CarrierId" = l."CarrierId"
			and r."PlanTypeId" = l."PlanTypeId"
		)
	where
		l."CompanyId" = ?
		and l."ImportDate" = ?
) as subquery
where
	"LifeEventCompare"."Id" = subquery."LifeEventCompareId"
