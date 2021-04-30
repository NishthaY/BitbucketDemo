SELECT
    "User".*
FROM
    "User"
    join "UserCompany" on ( "User"."Id" = "UserCompany"."UserId" )
where
    "UserCompany"."CompanyId" = ?
