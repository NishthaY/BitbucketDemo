insert into "CompanyCommissionWorker" ( "CompanyId", "ImportDate", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "MonthlyCost" )
select
  d."CompanyId"
  , d."ImportDate"
  , ld."LifeId"
  , wd."CarrierId"
  , wd."PlanTypeId"
  , wd."PlanId"
  , sum(coalesce(rd."MonthlyCost", d."MonthlyCost" )) as "MonthlyCost"
from
  "ImportData" d
  left join "RelationshipData" rd on ( rd."ImportDataId" = d."Id" )
  join "LifeData" ld on ( ld."ImportDataId" = d."Id" )
  join "CompanyLife" on ( "CompanyLife"."CompanyId" = d."CompanyId" and "CompanyLife"."Id" = ld."LifeId" and "CompanyLife"."Enabled" = true )
  join "WashedData" wd on ( wd."ImportDataId" = d."Id" )
where
  d."CompanyId" = ?
  and d."ImportDate" = ?
GROUP BY
  d."CompanyId", d."ImportDate", ld."LifeId", wd."CarrierId", wd."PlanTypeId", wd."PlanId"