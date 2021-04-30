select
	"PlanId" as "BestGuess_Before-PlanId"
from
	"RetroData"
where
	"CompanyId" = ?
	and "ImportDate" = to_date(?, 'MM/DD/YYYY') - interval '1 month'
	and "LifeId" = ?
	and "CoverageTierId" in ( {LIST} )
group by "PlanId"
order by "PlanId" asc
