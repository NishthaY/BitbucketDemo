select
    now."Tag"
     , CASE
           WHEN ( now."End" is not null and now."Start" is not null and now."End" >= now."Start" )
               THEN TO_CHAR( (extract(epoch from now."End") - extract(epoch from now."Start") || ' second')::interval, 'HH24:MI:SS')
           ELSE '-'
    END as "Time"
     , CASE
           WHEN ( prev."End" is not null and prev."Start" is not null and prev."End" >= prev."Start" )
               THEN TO_CHAR( (extract(epoch from prev."End") - extract(epoch from prev."Start") || ' second')::interval, 'HH24:MI:SS')
           ELSE '-'
    END as "Previous"
     , CASE
           WHEN now."End" is null OR now."End" <= now."Start" then ''
           WHEN (extract(epoch from now."End") - extract(epoch from now."Start")) -  (extract(epoch from prev."End") - extract(epoch from prev."Start")) = 0 THEN ''
           ELSE (extract(epoch from now."End") - extract(epoch from now."Start")) -  (extract(epoch from prev."End") - extract(epoch from prev."Start")) || ' seconds'
    END as "Change"
    , now."Start"
    , now."End"
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
  and now."ParentTag" = ?
  and now."Start" >= '{START}'::date
  and now."End" >= '{END}'::date
order by now."Id" asc