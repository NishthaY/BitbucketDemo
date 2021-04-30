select
	"Audit"."Description"
	, format('%s %s', "User"."FirstName", "User"."LastName") as "User"
from
	"Audit"
	join "Company" on ("Company"."Id" = "Audit"."CompanyId")
	join "User" on ( "User"."Id" = "Audit"."UserId")
where
	"Audit"."CompanyId" = ?
	and "Audit"."Description" <> 'SendEmail'
order by "Audit"."AuditDate" desc
limit 3
