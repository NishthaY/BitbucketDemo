select
  case when count(*) >= 1 then true else false end as is_manager
from
  "UserAcl"
  join "Acl" on ( "Acl"."Id" = "UserAcl"."AclId")
WHERE
  "UserAcl"."UserId" = ?
  and "UserAcl"."Target" is null
  and "Acl"."Name" in ('Manager', 'Parent Manager')
