select
	now."CoverageTierId"
from
	"RetroData" as now
where
	now."CompanyId" = ?
	and now."ImportDate" = ?
	and now."LifeId" = ?
	and now."PlanTypeCode" = ?
group by now."CompanyId", now."ImportDate", now."LifeId", now."PlanTypeCode", now."CoverageTierId"
order by now."CompanyId", now."ImportDate", now."LifeId", now."PlanTypeCode", now."CoverageTierId"
