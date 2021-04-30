select
  DATE_PART('hour', now()::time - "StartTime"::time) * 60 + DATE_PART('minute', now()::time - "StartTime"::time) + 1 as "Seconds"
  , CASE
    WHEN extract(year from age(now(), "ProcessQueue"."StartTime"::timestamp)) <> 0 then format('%s year(s) ago', extract(year from age(now(), "ProcessQueue"."StartTime"::timestamp)))
    WHEN extract(month from age(now(), "ProcessQueue"."StartTime"::timestamp)) <> 0 then format('%s month(s) ago', extract(month from age(now(), "ProcessQueue"."StartTime"::timestamp)))
    WHEN extract(day from age(now(), "ProcessQueue"."StartTime"::timestamp)) <> 0 then format('%s day(s) ago', extract(day from age(now(), "ProcessQueue"."StartTime"::timestamp)))
    WHEN extract(hour from age(now(), "ProcessQueue"."StartTime"::timestamp)) <> 0 then format('%s hour(s) ago', extract(hour from age(now(), "ProcessQueue"."StartTime"::timestamp)))
    WHEN extract(minute from age(now(), "ProcessQueue"."StartTime"::timestamp)) <> 0 then format('%s minute(s) ago', extract(minute from age(now(), "ProcessQueue"."StartTime"::timestamp)))
    WHEN extract(second from age(now(), "ProcessQueue"."StartTime"::timestamp)) <> 0 then format('%s second(s) ago', extract(second from age(now(), "ProcessQueue"."StartTime"::timestamp))::numeric::integer)
    else ''
    end as "Age"
from
  "ProcessQueue"
where
  "Id" = ?
