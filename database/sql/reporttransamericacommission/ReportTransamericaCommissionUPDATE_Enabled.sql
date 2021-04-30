with t as (
    SELECT
      "CompanyId",
      "ImportDate",
      "LifeId",
      "CarrierId",
      "PlanTypeId",
      "PlanId",
      "CoverageTierId",
      "MasterPolicy"
    FROM
      "ReportTransamericaCommission"
    WHERE
      "ReportTransamericaCommission"."CompanyId" = ?
      AND "ReportTransamericaCommission"."ImportDate" = ?
    GROUP BY
      "CompanyId", "ImportDate", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId", "MasterPolicy"
    HAVING count(*) = 1
)
update "ReportTransamericaCommission"
set "Enabled" = true
from t
WHERE
  "ReportTransamericaCommission"."CompanyId" = t."CompanyId"
  and "ReportTransamericaCommission"."ImportDate" = t."ImportDate"
  and "ReportTransamericaCommission"."LifeId" = t."LifeId"
  and "ReportTransamericaCommission"."CarrierId" = t."CarrierId"
  and "ReportTransamericaCommission"."PlanTypeId" = t."PlanTypeId"
  and "ReportTransamericaCommission"."PlanId" = t."PlanId"
  and "ReportTransamericaCommission"."CoverageTierId" = t."CoverageTierId"
  and "ReportTransamericaCommission"."MasterPolicy" = t."MasterPolicy"