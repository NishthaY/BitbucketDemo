select
	"MonthlyCost" as "BestGuess_Before-MonthlyCost"
from
	"RetroData"
where
	"CompanyId" = ?
	and "ImportDate" = to_date(?, 'MM/DD/YYYY') - interval '1 month'
	and "LifeId" = ?
	and "CoverageTierId" in ( {LIST} )
group by "MonthlyCost"
order by "MonthlyCost" asc
