select
	"Audit"."Description"
	, format('%s %s', "User"."FirstName", "User"."LastName") as "User"
from
	"Audit"
	join "CompanyParent" on ("CompanyParent"."Id" = "Audit"."CompanyParentId")
	join "User" on ( "User"."Id" = "Audit"."UserId")
where
	"Audit"."CompanyParentId" = ?
	and "Audit"."Description" <> 'SendEmail'
order by "Audit"."AuditDate" desc
limit 3
