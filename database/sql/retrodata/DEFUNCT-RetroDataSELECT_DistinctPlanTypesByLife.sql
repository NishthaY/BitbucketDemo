select
	now."PlanTypeCode"
from
	"RetroData" as now
where
	now."CompanyId" = ?
	and now."ImportDate" = ?
	and now."LifeId" = ?
group by now."CompanyId", now."ImportDate", now."LifeId", now."PlanTypeCode"
order by now."CompanyId", now."ImportDate", now."LifeId", now."PlanTypeCode"
