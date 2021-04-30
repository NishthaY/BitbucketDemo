select
	"Volume" as "BestGuess_Before-Volume"
from
	"RetroData"
where
	"CompanyId" = ?
	and "ImportDate" = to_date(?, 'MM/DD/YYYY') - interval '1 month'
	and "LifeId" = ?
	and "CoverageTierId" in ( {LIST} )
group by "Volume"
order by "Volume" asc
