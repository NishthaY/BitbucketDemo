select
    "ProcessQueue"."Id" as "JobId"
     , "Controller" as "JobName"
     , case when ( "Company"."CompanyName" is null ) then "CompanyParent"."Name" else "Company"."CompanyName" end as "CompanyName"
     , format('%s %s', "User"."FirstName", "User"."LastName") as "User"
     , CASE
           WHEN extract(year from age(now(), "ProcessQueue"."StartTime"::timestamp)) <> 0 then format('%s year(s) ago', extract(year from age(now(), "ProcessQueue"."StartTime"::timestamp)))
           WHEN extract(month from age(now(), "ProcessQueue"."StartTime"::timestamp)) <> 0 then format('%s month(s) ago', extract(month from age(now(), "ProcessQueue"."StartTime"::timestamp)))
           WHEN extract(day from age(now(), "ProcessQueue"."StartTime"::timestamp)) <> 0 then format('%s day(s) ago', extract(day from age(now(), "ProcessQueue"."StartTime"::timestamp)))
           WHEN extract(hour from age(now(), "ProcessQueue"."StartTime"::timestamp)) <> 0 then format('%s hour(s) ago', extract(hour from age(now(), "ProcessQueue"."StartTime"::timestamp)))
           WHEN extract(minute from age(now(), "ProcessQueue"."StartTime"::timestamp)) <> 0 then format('%s minute(s) ago', extract(minute from age(now(), "ProcessQueue"."StartTime"::timestamp)))
           WHEN extract(second from age(now(), "ProcessQueue"."StartTime"::timestamp)) <> 0 then format('%s second(s) ago', extract(second from age(now(), "ProcessQueue"."StartTime"::timestamp))::numeric::integer)
           else ''
    end as "Started"
     , case when "Wizard"."RecentActivity" is null then "WorkflowProgressProperty"."Value" else "Wizard"."RecentActivity" end as "RecentActivity"
from
    "ProcessQueue"
        left join "Company" on ( "Company"."Id" = "ProcessQueue"."CompanyId" )
        left join "CompanyParent" on ("CompanyParent"."Id" = "ProcessQueue"."CompanyParentId" )
        join "User" on ( "User"."Id" = "ProcessQueue"."UserId" )
        left join "Wizard" on ( "Company"."Id" = "Wizard"."CompanyId")
        left join "WorkflowProgressProperty" on
        (
                    "WorkflowProgressProperty"."Name" = 'recent_activity' and
                    (
                            ("WorkflowProgressProperty"."Identifier" = "ProcessQueue"."CompanyParentId" and "IdentifierType" = 'companyparent' and "ProcessQueue"."CompanyId" is null) OR
                            ("WorkflowProgressProperty"."Identifier" = "ProcessQueue"."CompanyId" and "IdentifierType" = 'company')
                        )
            )
where
        1=1
  and ("ProcessQueue"."StartTime" is not null AND "ProcessQueue"."EndTime" is null ) -- running
  and UPPER("ProcessQueue"."Function") <> 'SCHEDULE'
order by "QueueTime" desc