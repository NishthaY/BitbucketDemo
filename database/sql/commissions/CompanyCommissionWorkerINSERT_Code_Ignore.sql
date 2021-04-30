insert into "CompanyCommissionWorker" ( "CompanyId", "ImportDate", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId" )
select
  c."CompanyId", c."ImportDate", c."LifeId", c."CarrierId", c."PlanTypeId", c."PlanId", c."CoverageTierId"
from
  "CompanyCommissionDataCompare" c
  join "CompanyCommissionData" d on ( d."CompanyId" = c."CompanyId" and d."ImportDate" = c."ImportDate" and d."LifeId" = c."LifeId" and d."CarrierId" = c."CarrierId" and d."PlanTypeId" = c."PlanTypeId" and d."PlanId" = c."PlanId" and d."CoverageTierId" = c."CoverageTierId"  )
where
  c."CompanyId" = ?
  and c."ImportDate" = ?
  and c."Code" is null
  and d."LostDate" is not null
  and d."LostDate" <= c."ImportDate"