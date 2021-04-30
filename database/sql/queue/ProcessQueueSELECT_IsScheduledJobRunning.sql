select
  case when count(*) = 0 then false else true end as "IsRunning"
from
  "ProcessQueue"
where
  "EndTime" is null
  and "StartTime" is not null
  and UPPER("Controller") = UPPER(?)
  and UPPER("Function") = 'SCHEDULE'