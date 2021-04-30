select
    "GroupCode" as column
from
    "CompanyParentPreference"
where
    "CompanyParentId" = ?
    and "Group" = 'column_map'
    and "Value" = ?
