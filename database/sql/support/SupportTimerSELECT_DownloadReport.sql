select
    ? as "Depth"
    , now."ParentTag"
     , now."Tag"
     , CASE
           WHEN ( now."End" is not null and now."Start" is not null and now."End" >= now."Start" )
               THEN TO_CHAR( (extract(epoch from now."End") - extract(epoch from now."Start") || ' second')::interval, 'HH24:MI:SS')
           ELSE '-'
    END as "Time"
     , now."Start"
     , now."End"
     , extract(epoch from now."Start") as "EpochStart"
     , extract(epoch from now."End") as "EpochEnd"
     , case
           when ( now."End" is not null and now."Start" is not null and now."End" >= now."Start" )
               THEN extract(epoch from now."End") -  extract(epoch from now."Start")
           ELSE null
    END as "Seconds"
from
    "SupportTimer" now
        left join "SupportTimer" prev on (
                prev."CompanyId" = now."CompanyId"
            and prev."ImportDate" = (now."ImportDate" + interval '-1 month')
            and prev."ParentTag" = now."ParentTag"
            and prev."Tag" = now."Tag"
        )
where
        now."CompanyId" = ?
  and now."ImportDate" = ?
  and now."ParentTag" = ''
order by now."Id" asc