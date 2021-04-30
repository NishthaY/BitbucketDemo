update
    "CompanyParentMapCompany"
set
    "CompanyId" = ?
    , "UserDescription" = ?
    , "Ignored" = ?
where
    "CompanyParentId" = ?
    and "CompanyNormalized" = ?