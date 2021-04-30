update "CompanyPreference" set
    "Value" = ?
where
    "CompanyId" = ?
    AND "Group" = ?
    AND "GroupCode" = ?
