select
	now."LifeId"
	, now."PlanTypeCode"
	, now."CoverageTierId"
from
	"RetroData" as now
where
	now."CompanyId" = ?
	and now."ImportDate" = ?
order by
	now."CoverageTierId" asc
