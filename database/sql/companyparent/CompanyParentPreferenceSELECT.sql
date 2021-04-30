select
    "Group" as group
    , "GroupCode" as group_code
    , "Value" as value
from
    "CompanyParentPreference"
where
    "CompanyParentId" = ?
    and "Group" = ?
    and "GroupCode" = ?
