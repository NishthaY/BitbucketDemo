select
  *
from
  "CompanyCommissionDataCompare" compare
  join "CompanyCommissionData" data on ( data."CompanyId" = compare."CompanyId" and data."ImportDate" = compare."ImportDate" and data."LifeId" = compare."LifeId" and data."CarrierId" = compare."CarrierId" and data."PlanTypeId" = compare."PlanTypeId" and data."PlanId" = compare."PlanId" and data."CoverageTierId" = compare."CoverageTierId" )
WHERE
  compare."CompanyId" = ?
  and compare."ImportDate" = ?
  and compare."Code" in ('ADD','REDUCE','INCREASE')