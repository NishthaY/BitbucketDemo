update "CompanyParentMapCompany" set "Ignored" = false where "Id" in
(
    select
        m."Id"
    from
        "CompanyParentImportData" d
        join "CompanyParentMapCompany" m on ( m."CompanyNormalized" = trim(upper(d."Company")))
    where
        d."CompanyParentId" = ?
    and "Ignored" = true
)