update "CompanyCommissionDataCompare" as c set
  "Code" = 'WARNING'
  , "Description" = 'Multiple tiers found per life and plan.  Unable to calculate commissions for life.'
from "CompanyCommissionWorker" as w
where
  c."CompanyId" = ?
  and c."ImportDate" = ?
  and c."CompanyId" = w."CompanyId"
  and c."ImportDate" = w."ImportDate"
  and c."LifeId" = w."LifeId"
  and c."CarrierId" = w."CarrierId"
  and c."PlanTypeId" = w."PlanTypeId"
  and c."PlanId" = w."PlanId"
  and c."Code" is null