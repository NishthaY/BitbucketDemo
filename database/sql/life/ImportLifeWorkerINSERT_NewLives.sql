insert into "ImportLifeWorker" ( "CompanyId", "ImportDate", "LifeKey" )
select
    d."CompanyId"
     , d."ImportDate"
     , l."LifeKey"
from
    "LifeData" d
    join "CompanyLife" l on (l."Id" = d."LifeId")
where
    d."CompanyId" = ?
    and d."ImportDate" = ?
    and d."NewLifeFlg" = true
group by
    d."CompanyId"
       , d."ImportDate"
       , l."LifeKey"
