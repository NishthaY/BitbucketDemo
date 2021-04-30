select
	case when count(*) = 0 then true else false end as "IsFirstImport"
from
	"ImportData"
where
	"CompanyId" = ?
	and "ImportDate" = to_date(?, 'MM/DD/YYYY') - interval '1 month'
