update "CompanyCommissionDataCompare" c
set "Code" = ?, "Description" = ?
FROM
  "CompanyCommissionWorker" w
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