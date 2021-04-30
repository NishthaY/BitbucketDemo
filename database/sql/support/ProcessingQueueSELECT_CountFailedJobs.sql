select
	count(*) as "Count"
from
	"ProcessQueue"
	left join "HistoryFailedJob" on
	(
		"HistoryFailedJob"."UserId" = ?
		and "HistoryFailedJob"."JobId" = "ProcessQueue"."Id"
	)
where
	1=1
	and ( "ProcessQueue"."Failed" = true ) -- failed
    and ( "ProcessQueue"."EndTime" > NOW() - interval '1 month') -- only failed in the past month.
	and ( "HistoryFailedJob"."JobId" is null ) -- only items this user has not yet cleared.
	and UPPER("ProcessQueue"."Function") <> 'SCHEDULE'
