select
    "GroupCode" as column
from
    "CompanyPreference"
where
    "CompanyId" = ?
    and "Group" = 'column_map'
    and "Value" = ?
