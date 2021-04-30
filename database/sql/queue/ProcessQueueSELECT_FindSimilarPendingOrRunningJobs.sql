select
 *
from "ProcessQueue"
where
    "Controller" = ?
    and "Function" = ?
    and ( "StartTime" is null or "EndTime" is null )
