select
	DATE_PART('hour', now()::time - "StartTime"::time) * 60 + DATE_PART('minute', now()::time - "StartTime"::time) + 1 as "Minutes Running"
	, "StartTime" as "Started At"
	, "Controller" as "Business Entity"
	, "Payload" as "Parameters"
	, "Id" as "JobId"
	, "ProcessId" as "ProcessId"

from
	"ProcessQueue"
where
	"EndTime" is null
	and "StartTime" is not null
	and UPPER("Function") <> 'SCHEDULE'
order by "StartTime" desc
