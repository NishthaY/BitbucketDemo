select
	count(*) as "Count"
from
	"ProcessQueue"
where
	1=1
	and ("ProcessQueue"."StartTime" is not null AND "ProcessQueue"."EndTime" is null ) -- running
	and UPPER("ProcessQueue"."Function") <> 'SCHEDULE'