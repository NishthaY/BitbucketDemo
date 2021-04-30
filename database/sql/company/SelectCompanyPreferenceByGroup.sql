select
    "Group" as group
    , "GroupCode" as group_code
    , "Value" as value
from
    "CompanyPreference"
where
    "CompanyId" = ?
    and "Group" = ?
