update "RetroData" set
	"Before-CoverageStartDate"=subquery."CoverageStartDate"
	,"Before-CoverageEndDate"=subquery."CoverageEndDate"
	,"Before-MonthlyCost"=subquery."MonthlyCost"
	,"Before-Volume"=subquery."Volume"
	,"Before-PlanId"=subquery."PlanId"
from (
	select
		now."Id"
		, prev."CoverageStartDate" as "CoverageStartDate"
		, prev."CoverageEndDate" as "CoverageEndDate"
		, prev."MonthlyCost" as "MonthlyCost"
		, prev."Volume" as "Volume"
		, prev."PlanId" as "PlanId"
	from
		"RetroData" as now
		left join "RetroData" as prev on (
			prev."ImportDate" = to_date(?, 'MM/DD/YYYY') - interval '1 month'
			and prev."CompanyId" = now."CompanyId"
			and prev."CarrierId" = now."CarrierId"
			and prev."PlanTypeId" = now."PlanTypeId"
			and prev."PlanId" = now."PlanId"
			and prev."CoverageTierId" = now."CoverageTierId" -- Coverage Tier did not change.
			and prev."LifeId" = now."LifeId"
		)
	where
		now."CompanyId" = ?
		and now."ImportDate" = ?
) as subquery
where "RetroData"."Id" = subquery."Id"
