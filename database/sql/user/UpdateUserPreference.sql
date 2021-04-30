update "UserPreference" set
    "Value" = ?
where
    "UserId" = ?
    AND "Group" = ?
    AND "GroupCode" = ?
