select
    *
from
    "ProcessQueue"
where
    "Controller" = ?
    and "Function" = ?
    and ( "StartTime" is null and "EndTime" is null )
