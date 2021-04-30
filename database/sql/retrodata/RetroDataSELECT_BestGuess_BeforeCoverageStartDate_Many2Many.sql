select
	"CoverageStartDate" as "BestGuess_Before-CoverageStartDate"
from
	"RetroData"
where
	"CompanyId" = ?
	and "ImportDate" = to_date(?, 'MM/DD/YYYY') - interval '1 month'
	and "LifeId" = ?
	and "CoverageTierId" in ( {LIST} )
group by "CoverageStartDate"
order by "CoverageStartDate" asc
