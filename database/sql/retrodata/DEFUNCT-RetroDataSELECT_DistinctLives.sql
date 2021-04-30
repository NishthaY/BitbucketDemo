select
	now."LifeId"
from
	"RetroData" as now
where
	now."CompanyId" = ?
	and now."ImportDate" = ?
group by now."CompanyId", now."ImportDate", now."LifeId"
order by now."CompanyId", now."ImportDate", now."LifeId"
