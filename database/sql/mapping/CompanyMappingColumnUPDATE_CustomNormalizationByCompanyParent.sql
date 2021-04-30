update
    "CompanyMappingColumn" set "NormalizationRegEx" = ?
where
    "Name" = ?
    and "CompanyId" in
    (
        select
            "CompanyId"
        from
            "CompanyParentCompanyRelationship"
        where
              "CompanyParentId" = ?
    )