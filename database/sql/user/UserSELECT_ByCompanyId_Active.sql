SELECT
    "User"."Id" as user_id
    , "User"."EmailAddress" as email_address
    , "User"."FirstName" as first_name
    , "User"."LastName" as last_name
    , "User"."Enabled" as enabled
    , null as is_manager
FROM
    "User"
    left join "UserCompany" on ( "UserCompany"."UserId" = "User"."Id" )
    left join "Company" on ( "Company"."Id" = "UserCompany"."CompanyId" )
where
    "Company"."Id" = ?
    and "User"."Deleted" = false
