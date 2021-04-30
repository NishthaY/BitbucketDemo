select
	case when count("Id") > 0 then true else false end as "HasLivesToCompare"
from
	"CompanyLifeCompare"
where
	"CompanyLifeCompare"."CompanyId" = ?
	and "CompanyLifeCompare"."ImportDate" = ?
	and "CompanyLifeCompare"."AutoSelected" = false
