select
	"Controller" as "JobName"
     , case when ( "Company"."CompanyName" is null ) then "CompanyParent"."Name" else "Company"."CompanyName" end as "CompanyName"
	, format('%s %s', "User"."FirstName", "User"."LastName") as "User"
	, CASE
		WHEN extract(year from age(now(), "ProcessQueue"."QueueTime"::timestamp)) <> 0 then format('%s year(s) ago', extract(year from age(now(), "ProcessQueue"."QueueTime"::timestamp)))
		WHEN extract(month from age(now(), "ProcessQueue"."QueueTime"::timestamp)) <> 0 then format('%s month(s) ago', extract(month from age(now(), "ProcessQueue"."QueueTime"::timestamp)))
		WHEN extract(day from age(now(), "ProcessQueue"."QueueTime"::timestamp)) <> 0 then format('%s day(s) ago', extract(day from age(now(), "ProcessQueue"."QueueTime"::timestamp)))
		WHEN extract(hour from age(now(), "ProcessQueue"."QueueTime"::timestamp)) <> 0 then format('%s hour(s) ago', extract(hour from age(now(), "ProcessQueue"."QueueTime"::timestamp)))
		WHEN extract(minute from age(now(), "ProcessQueue"."QueueTime"::timestamp)) <> 0 then format('%s minute(s) ago', extract(minute from age(now(), "ProcessQueue"."QueueTime"::timestamp)))
		WHEN extract(second from age(now(), "ProcessQueue"."QueueTime"::timestamp)) <> 0 then format('%s second(s) ago', extract(second from age(now(), "ProcessQueue"."QueueTime"::timestamp)))
		else ''
	end as "Requested"
from
	"ProcessQueue"
	left join "Company" on ("Company"."Id" = "ProcessQueue"."CompanyId" )
    left join "CompanyParent" on ("CompanyParent"."Id" = "ProcessQueue"."CompanyParentId" )
	join "User" on ( "User"."Id" = "ProcessQueue"."UserId" )
where
	1=1
	and "ProcessQueue"."StartTime" is null -- waiting
	and UPPER("ProcessQueue"."Function") <> 'SCHEDULE'
order by "ProcessQueue"."QueueTime" desc
