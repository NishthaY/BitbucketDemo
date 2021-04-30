select
    CASE when count(*) >= 1 THEN true ELSE false END as has_permission
from
    "UserAcl"
where
    "UserId" = ?
    and "AclId" = ?
