select
	"Acl".*
	, "UserAcl"."Target"
	, "UserAcl"."TargetId"
from
	"User"
	join "UserAcl" on ( "User"."Id" = "UserAcl"."UserId" )
	join "Acl" on ( "Acl"."Id" = "UserAcl"."AclId" )
where
	"User"."Id" = ?
