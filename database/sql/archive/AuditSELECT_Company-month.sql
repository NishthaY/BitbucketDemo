select
    "Audit"."Description"
    , format('%s %s', "User"."FirstName", "User"."LastName") as "User"
    , "Audit"."AuditDate" at TIME ZONE ? as "Timestamp"
    , "Audit"."Payload"
from
	"Audit"
	join "Company" on ("Company"."Id" = "Audit"."CompanyId")
	join "User" on ( "User"."Id" = "Audit"."UserId")
where
	"Audit"."CompanyId" = ?
	--and "Audit"."AuditDate" >= now() - interval '1' day		-- recent
	--and "Audit"."AuditDate" >= now() - interval '7' day		-- week
	and "Audit"."AuditDate" >= now() - interval '1' month	-- month
	--and "Audit"."AuditDate" >= now() - interval '6' month	-- months
	--and "Audit"."AuditDate" >= now() - interval '1' year	-- year
    and "Audit"."Description" <> 'SendEmail'
order by "Audit"."AuditDate" desc
