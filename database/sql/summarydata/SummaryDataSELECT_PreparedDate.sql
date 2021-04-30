select
	to_char("PreparedDate", 'MM/DD/YYYY') as "PreparedDate"
from
	"SummaryData"
where
	"CompanyId" = ?
	and "ImportDate" = ?
group by "PreparedDate"
limit 1
