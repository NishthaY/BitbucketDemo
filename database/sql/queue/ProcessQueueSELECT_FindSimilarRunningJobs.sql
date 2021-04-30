select
case when ( count(*) = 0 ) then false else true end as "PendingOrRunningJobs"
from "ProcessQueue"
where
    "Controller" = ?
    and "Function" = ?
    and ( "StartTime" is null or "EndTime" is null )
