select
  u."EmailAddress"
from
  "UserAcl" ua
  join "Acl" a on ( a."Id" = ua."AclId")
  join "User" u on ( ua."UserId" = u."Id")
where
  a."Name" = 'All'