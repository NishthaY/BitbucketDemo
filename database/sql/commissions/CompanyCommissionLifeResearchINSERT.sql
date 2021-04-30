insert into "CompanyCommissionLifeResearch" ( "CompanyId", "ImportDate", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "ImportDataId")
select
  cc."CompanyId"
  , cc."ImportDate"
  , cc."LifeId"
  , cc."CarrierId"
  , cc."PlanTypeId"
  , cc."PlanId"
  , d."Id" as "ImportDataId"
from
  "CompanyCommission" cc
  join "CompanyLife" cl on ( cl."Id" = cc."LifeId" and "Enabled" = true )
  join "ImportLife" il on ( il."CompanyId" = cc."CompanyId" and il."ImportDate" = cc."ImportDate" and il."LifeKey" = cl."LifeKey" )
  left join "ImportData" d on ( d."Id" = il."ImportDataId")
  join "WashedData" w on ( w."ImportDataId" = d."Id" and w."CarrierId" = cc."CarrierId" and w."PlanTypeId" = cc."PlanTypeId" and w."PlanId" = cc."PlanId" )
where
  cc."CompanyId" = ?
  and cc."ImportDate" = ?