select
  *
from
  "UserAcl" ua
  join "Acl" a on ( a."Id" = ua."AclId")
where
  ua."UserId" = ?
  and a."Name" = 'All'