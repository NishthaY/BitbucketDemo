select
case when (count(*) = 0) then false else true end as "PendingOrRunningJobs"
from "ProcessQueue"
where
    "Payload" like '["%","","COMPANYPARENT_ID"]'
    and ( "StartTime" is null or "EndTime" is null )
    and UPPER("Function") <> 'SCHEDULE'
