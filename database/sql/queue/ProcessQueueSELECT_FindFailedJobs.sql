select
	DATE_PART('hour', "EndTime"::time - "StartTime"::time) * 60 + DATE_PART('minute', "EndTime"::time - "StartTime"::time) as "Minutes Executed"
	, "StartTime" as "Started At"
	, "Controller" as "Business Entity"
	, "Payload" as "Parameters"
	, "ErrorMessage" as "Failure Reason"

from
	"ProcessQueue"
where
	"Failed" = true
	and UPPER("Function") <> 'SCHEDULE'
order by "StartTime" desc
