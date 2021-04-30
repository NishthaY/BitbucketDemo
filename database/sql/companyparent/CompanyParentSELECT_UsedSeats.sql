select
	count(*) as "UsedSeats"
from
	"Company"
	join "CompanyParentCompanyRelationship" on ( "CompanyParentCompanyRelationship"."CompanyId" = "Company"."Id" )
 where
	"Company"."Enabled" = true
	and "CompanyParentCompanyRelationship"."CompanyParentId" = ?
