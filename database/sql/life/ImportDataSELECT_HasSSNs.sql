select
	CASE WHEN count("SSN") <> 0 THEN true ELSE false END as "HasSSNs"
from
	"ImportData"
where
	"CompanyId" = ?
	and "ImportDate" = ?
	and ( "SSN" is not null OR "SSN" <> '' )
