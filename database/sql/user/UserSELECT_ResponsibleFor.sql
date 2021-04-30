select
  "User".*
  , case when r."UserId" is null then false else true end as "ResponsibleFor"
from
  "CompanyParent" cp
  join "UserCompanyParentRelationship" on ( "UserCompanyParentRelationship"."CompanyParentId" = cp."Id")
  join "User" on ( "UserCompanyParentRelationship"."UserId" = "User"."Id")
  left join "UserResponsibleForCompany" r on
  (
    r."UserId" = "User"."Id"
    and r."ParentCompanyId" = cp."Id"
    and r."CompanyId" = ?
  )
WHERE
  cp."Id" = ?
  and "User"."Enabled" = true
  and "User"."Deleted" = false
