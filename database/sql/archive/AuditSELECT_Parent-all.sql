select
	"Audit"."Description"
	, format('%s %s', "User"."FirstName", "User"."LastName") as "User"
	, "Audit"."AuditDate" at TIME ZONE ? as "Timestamp"
	, "Audit"."Payload"
from
	"Audit"
	join "CompanyParent" on ("CompanyParent"."Id" = "Audit"."CompanyParentId")
	join "User" on ( "User"."Id" = "Audit"."UserId")
where
	"Audit"."CompanyParentId" = ?
	--and "Audit"."AuditDate" >= now() - interval '1' day		-- recent
	--and "Audit"."AuditDate" >= now() - interval '7' day		-- week
	--and "Audit"."AuditDate" >= now() - interval '1' month	-- month
	--and "Audit"."AuditDate" >= now() - interval '6' month	-- months
	--and "Audit"."AuditDate" >= now() - interval '1' year	-- year
	and "Audit"."Description" <> 'SendEmail'
order by "Audit"."AuditDate" desc
