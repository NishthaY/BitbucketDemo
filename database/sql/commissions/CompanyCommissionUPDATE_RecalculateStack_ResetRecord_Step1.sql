update "CompanyCommission" cc set "ResetRecord" = false
from "CompanyCommissionWorker" w
where
  w."CompanyId" = ?
  and w."ImportDate" = ?
  and w."CompanyId" = cc."CompanyId"
  and w."ImportDate" = cc."ImportDate"
  and w."LifeId" = cc."LifeId"
  and w."CarrierId" = cc."CarrierId"
  and w."PlanTypeId" = cc."PlanTypeId"
  and w."PlanId" = cc."PlanId"