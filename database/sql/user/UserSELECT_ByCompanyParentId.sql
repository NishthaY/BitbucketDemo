SELECT
    "User"."Id" as user_id
    , "User"."EmailAddress" as email_address
    , "User"."FirstName" as first_name
    , "User"."LastName" as last_name
    , "User"."Enabled" as enabled
    , null as is_manager
FROM
    "User"
    left join "UserCompanyParentRelationship" on ( "UserCompanyParentRelationship"."UserId" = "User"."Id" )
    left join "CompanyParent" on ( "CompanyParent"."Id" = "UserCompanyParentRelationship"."CompanyParentId" )
where
    "CompanyParent"."Id" = ?
    and "User"."Deleted" = false
