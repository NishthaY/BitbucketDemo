SELECT
  "Company".*
  , case when r."UserId" is null then false else true end as "ResponsibleFor"
from
  "CompanyParent" cp
  join "CompanyParentCompanyRelationship" on ( "CompanyParentCompanyRelationship"."CompanyParentId" = cp."Id")
  join "Company" on ( "CompanyParentCompanyRelationship"."CompanyId" = "Company"."Id")
  left join "UserResponsibleForCompany" r on
  (
    1=1
    and r."ParentCompanyId" = cp."Id"
    and r."CompanyId" = "Company"."Id"
    and "r"."UserId" = ?
  )
WHERE
  cp."Id" = ?
  and "Company"."Enabled" = true
order by
  "Company"."CompanyName" asc