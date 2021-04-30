select
	CASE WHEN count("Relationship") <> 0 THEN true ELSE false END as "HasRelationships"
from
	"ImportData"
where
	"CompanyId" = ?
	and "ImportDate" = ?
	and ( "Relationship" is not null OR "Relationship" <> '' )
