select
	*
from
	"CompanyCarrier"
where
	"CompanyCarrier"."CompanyId" = ?
	and "CompanyCarrier"."UserDescription" is null
