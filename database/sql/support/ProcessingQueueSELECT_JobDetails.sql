select
    to_char("ProcessQueue"."QueueTime"::timestamp, 'MM/DD/YYY HH12:MI:SS AM') as queued
     ,to_char("ProcessQueue"."StartTime"::timestamp, 'MM/DD/YYY HH12:MI:SS AM') as started
     ,to_char("ProcessQueue"."EndTime"::timestamp, 'MM/DD/YYY HH12:MI:SS AM') as ended
     ,"Controller" as job_name
     ,"ErrorMessage" as message
     , "CompanyParent"."Name" as "company"
     , "CompanyParent"."Id"  as "company_id"
     , format('%s %s', "User"."FirstName", "User"."LastName") as user
     , "User"."Id" as user_id
from
    "ProcessQueue"
        join "CompanyParent" on ("CompanyParent"."Id" = "CompanyParentId" )
        join "User" on ("User"."Id" = "ProcessQueue"."UserId" )
where
        "ProcessQueue"."Id" = ?
  and "CompanyId" is null

UNION

select
    to_char("ProcessQueue"."QueueTime"::timestamp, 'MM/DD/YYY HH12:MI:SS AM') as queued
     ,to_char("ProcessQueue"."StartTime"::timestamp, 'MM/DD/YYY HH12:MI:SS AM') as started
     ,to_char("ProcessQueue"."EndTime"::timestamp, 'MM/DD/YYY HH12:MI:SS AM') as ended
     ,"Controller" as job_name
     ,"ErrorMessage" as message
     , "Company"."CompanyName" as "company"
     , "Company"."Id"  as "company_id"
     , format('%s %s', "User"."FirstName", "User"."LastName") as user
     , "User"."Id" as user_id
from
    "ProcessQueue"
        join "Company" on ("Company"."Id" = "ProcessQueue"."CompanyId" )
        join "User" on ("User"."Id" = "ProcessQueue"."UserId" )
where
        "ProcessQueue"."Id" = ?