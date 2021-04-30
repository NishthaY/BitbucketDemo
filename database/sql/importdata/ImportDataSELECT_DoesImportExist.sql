select
	case when count(*) <> 0 then true else false end as "ImportExists"
from
	"ImportData"
where
	"CompanyId" = ?
	and "ImportDate" = ?
