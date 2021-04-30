select
	to_char("ProcessQueue"."QueueTime"::timestamp, 'MM/DD/YYY HH12:MI:SS AM') as queued
	,to_char("ProcessQueue"."StartTime"::timestamp, 'MM/DD/YYY HH12:MI:SS AM') as started
	,to_char("ProcessQueue"."EndTime"::timestamp, 'MM/DD/YYY HH12:MI:SS AM') as ended
	,"Controller" as job_name
	,"ErrorMessage" as message
	, "Company"."CompanyName" as company
    , "Company"."Id" as company_id
	, format('%s %s', "User"."FirstName", "User"."LastName") as user
    , "User"."Id" as user_id
from
	"ProcessQueue"
	join "Company" on ("Company"."Id" = substring("Payload" from ',"(.*?)"' )::integer )
	join "User" on ("User"."Id" = substring("Payload" from '"(.*?)"' )::integer )
where
	"ProcessQueue"."ProcessId" = ?
order by "ProcessQueue"."StartTime" desc
limit 1
