SELECT
    "User"."Id" as user_id
    , "User"."EmailAddress" as email_address
    , "User"."FirstName" as first_name
    , "User"."LastName" as last_name
    , "User"."Password" as password
    , "Company"."CompanyName" as company
    , "Company"."Id" as company_id
    , "CompanyParent"."Name" as company_parent_name
    , "CompanyParent"."Id" as company_parent_id
    , "User"."Enabled" as enabled
    , null as is_manager
FROM
    "User"
    left join "UserCompany" on ( "UserCompany"."UserId" = "User"."Id" )
    left join "Company" on ( "Company"."Id" = "UserCompany"."CompanyId" )
    left join "UserCompanyParentRelationship" on ( "UserCompanyParentRelationship"."UserId" = "User"."Id" )
    left join "CompanyParent" on ( "CompanyParent"."Id" = "UserCompanyParentRelationship"."CompanyParentId" )
where
    "User"."Id" = ?
