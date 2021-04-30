select
    "Company"."Id" as "company_id"
    , "CompanyName" as "company_name"
    , "CompanyAddress" as "company_address"
    , "CompanyCity" as "company_city"
    , "CompanyState" as "company_state"
    , "CompanyPostal" as "company_postal"
    , "Company"."Enabled" as "enabled"
from
    "Company"
    left join "CompanyParentCompanyRelationship" on ( "CompanyParentCompanyRelationship"."CompanyId" = "Company"."Id" )
    left join "CompanyParent" on ( "CompanyParent"."Id" = "CompanyParentCompanyRelationship"."CompanyParentId" )
where
    "CompanyParent"."Id" = ?
