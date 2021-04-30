insert into "ImportLifeWarning" ( "CompanyId", "ImportDate", "LifeKey", "RecordCount" )
select
    cl."CompanyId"
     , w."ImportDate"
     , cl."LifeKey"
     , count(*) as "RecordCount"
from
    "CompanyLife" cl
    join "ImportLifeWorker" w on ( w."CompanyId" = cl."CompanyId" and w."LifeKey" = cl."LifeKey")
where
    cl."CompanyId" = ?
    and w."ImportDate" = ?
group by
    cl."CompanyId"
    , w."ImportDate"
    , cl."LifeKey"
having count(*) > 1