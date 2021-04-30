insert into "CompanyCommissionLife" ( "CompanyId", "ImportDate", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "ImportDataId" )
SELECT "CompanyId", "ImportDate", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "ImportDataId" FROM
  (
    SELECT
      "CompanyId"
      , "ImportDate"
      , "LifeId"
      , "CarrierId"
      , "PlanTypeId"
      , "PlanId"
      , "ImportDataId"
      , ROW_NUMBER() OVER (PARTITION BY ("CompanyId", "ImportDate", "LifeId", "CarrierId", "PlanTypeId", "PlanId") ORDER BY "ImportDataId" DESC) rn
    FROM
      "CompanyCommissionLifeResearch"
    WHERE
      "CompanyId" = ?
      and "ImportDate" = ?
  ) tmp WHERE rn = 1