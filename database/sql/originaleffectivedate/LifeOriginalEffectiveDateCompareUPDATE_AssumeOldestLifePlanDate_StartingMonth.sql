update "LifeOriginalEffectiveDateCompare" SET
  "OldestLifePlanEffectiveDate" = subquery."Calculated-EffectiveDate"
  , "OldestLifePlanDiscoveryDate" = subquery."ImportDate"
from (
       SELECT
         "CompanyId",
         "ImportDate",
         "LifeId",
         "CarrierId",
         "PlanTypeId",
         "PlanId",
         "Calculated-EffectiveDate"
       FROM
         (
           SELECT
             "CompanyId",
             "ImportDate",
             "LifeId",
             "CarrierId",
             "PlanTypeId",
             "PlanId",
             "Calculated-EffectiveDate",
             ROW_NUMBER()
             OVER (
               PARTITION BY ("CompanyId", "ImportDate", "LifeId", "CarrierId", "PlanTypeId", "PlanId")
               ORDER BY "Calculated-EffectiveDate" ASC ) rn
           FROM
             "LifeOriginalEffectiveDateCompare"
           WHERE
             "CompanyId" = ?
             AND "ImportDate" = ?
         ) tmp
       WHERE rn = 1
     ) as subquery
WHERE
  "LifeOriginalEffectiveDateCompare"."CompanyId" = subquery."CompanyId"
  and "LifeOriginalEffectiveDateCompare"."ImportDate" = subquery."ImportDate"
  and "LifeOriginalEffectiveDateCompare"."LifeId" = subquery."LifeId"
  and "LifeOriginalEffectiveDateCompare"."CarrierId" = subquery."CarrierId"
  and "LifeOriginalEffectiveDateCompare"."PlanTypeId" = subquery."PlanTypeId"
  and "LifeOriginalEffectiveDateCompare"."PlanId" = subquery."PlanId"
