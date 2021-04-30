select
	count(*) as "Count"
from
	"ProcessQueue"
where
	1=1
	and "ProcessQueue"."StartTime" is null -- waiting
	and UPPER("ProcessQueue"."Function") <> 'SCHEDULE'