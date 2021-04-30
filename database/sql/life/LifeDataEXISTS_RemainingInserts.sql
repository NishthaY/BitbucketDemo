select
  case when count(*) <> 0 then true else false end as remaining
from
  "ImportData"
  join "ImportLife" on ( "ImportLife"."ImportDataId" = "ImportData"."Id" )
  join "CompanyLife" on ( "CompanyLife"."CompanyId" = "ImportData"."CompanyId" and "CompanyLife"."LifeKey" = "ImportLife"."LifeKey" )
  left join "LifeData" on ( "ImportData"."Id" = "LifeData"."ImportDataId" )
where
  "ImportData"."CompanyId" = ?
  and "ImportData"."ImportDate" = ?
  and "CompanyLife"."Enabled" = true
  and "LifeData"."Id" is null
limit 1