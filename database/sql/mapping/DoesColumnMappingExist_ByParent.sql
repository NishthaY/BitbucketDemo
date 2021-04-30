select
    CASE WHEN ( count(*) >= 1 ) THEN true ELSE false END as mapped
from
    "CompanyParentPreference"
where
    "CompanyParentId" = ?
    and "Group" = 'column_map'
    and "Value" = ?
