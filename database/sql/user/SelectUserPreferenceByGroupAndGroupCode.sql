select
    "Group" as group
    , "GroupCode" as group_code
    , "Value" as value
from
    "UserPreference"
where
    "UserId" = ?
    and "Group" = ?
    and "GroupCode" = ?
