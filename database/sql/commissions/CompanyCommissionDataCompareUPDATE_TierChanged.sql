update "CompanyCommissionDataCompare" as c
SET "TierChanged" = true
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
  and c."CoverageTierId" = w."CoverageTierId"