select
  case when count(*) = 0 then false else true end as "IsAuthenticated"
from
  "AclActionRelationship"
  join "Acl" on ("Acl"."Id" = "AclActionRelationship"."AclId")
  join "AclAction" on ("AclAction"."Id" = "AclActionRelationship"."AclActionId")
WHERE
  LOWER("Acl"."Name") in ( {ACL_LIST} )
  and LOWER("AclAction"."Name") in ( {ACTION_LIST} )