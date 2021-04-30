insert into "CompanyCommissionWorker" ( "CompanyId", "ImportDate", "LifeId", "CarrierId", "PlanTypeId", "PlanId" )
SELECT
  compare."CompanyId",
  compare."ImportDate",
  compare."LifeId",
  compare."CarrierId",
  compare."PlanTypeId",
  compare."PlanId"
FROM
  "CompanyCommissionDataCompare" compare
  join "CompanyCommissionData" data on ( data."CompanyId" = compare."CompanyId" and data."ImportDate" = compare."ImportDate" and data."LifeId" = compare."LifeId" and data."CarrierId" = compare."CarrierId" and data."PlanTypeId" = compare."PlanTypeId" and data."PlanId" = compare."PlanId" and data."CoverageTierId" = compare."CoverageTierId" )
WHERE
  compare."CompanyId" = ?
  AND compare."ImportDate" = ?
  AND compare."Code" is null
  AND data."OEDCode" <> 'MISSING'
GROUP BY compare."CompanyId", compare."ImportDate", compare."LifeId", compare."CarrierId", compare."PlanTypeId", compare."PlanId"
HAVING count(*) > 1