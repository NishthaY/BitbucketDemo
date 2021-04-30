insert into "ImportLifeWorker" ( "CompanyId", "ImportDate", "LifeKey", "TargetId" )
select
    "CompanyId"
     , "ImportDate"
     , "LifeKey"
     , "Id"
from
(
    select
        life."CompanyId" as "CompanyId"
        , warn."ImportDate" as "ImportDate"
        , life."LifeKey" as "LifeKey"
        , life."Id" as "Id"
        , ROW_NUMBER() OVER (PARTITION BY life."LifeKey" order by life."MiddleName" desc, life."LastName" desc) as "Version"
    from
        "CompanyLife" life
        join "ImportLifeWarning" warn on (life."CompanyId" = warn."CompanyId" and life."LifeKey" = warn."LifeKey")
    where
        warn."CompanyId" = ?
        and warn."ImportDate" = ?
) as tbl
where tbl."Version" <> 1