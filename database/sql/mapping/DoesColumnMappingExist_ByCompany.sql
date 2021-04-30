select
    CASE WHEN ( count(*) >= 1 ) THEN true ELSE false END as mapped
from
    "CompanyPreference"
where
    "CompanyId" = ?
    and "Group" = 'column_map'
    and "Value" = ?
