select TO_CHAR(("TotalSeconds" || ' second')::interval, 'HH24:MI:SS') as "EstimatedRunTime" from (
    select sum( "TotalSeconds" ) as "TotalSeconds" from (
        select
               case when ( extract(epoch from "End") - extract(epoch from "Start") ) < 0 then 0 else extract(epoch from "End") - extract(epoch from "Start") END as "TotalSeconds"
               --extract(epoch from "End") - extract(epoch from "Start") as "TotalSeconds"
        from "SupportTimer"
        where "CompanyId" = ?
          and "ImportDate" = ?
          and "ParentTag" = ''
          and ("Start" is not null or "End" is not null)
    ) as x
) as y
