update "CompanyParentPreference" set
    "Value" = ?
where
    "CompanyParentId" = ?
    AND "Group" = ?
    AND "GroupCode" = ?
