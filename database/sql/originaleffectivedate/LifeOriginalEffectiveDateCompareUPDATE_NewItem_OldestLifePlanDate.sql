update "LifeOriginalEffectiveDateCompare" c
set
  "OldestLifePlanEffectiveDate" = ?
  , "OldestLifePlanDiscoveryDate" = ?
WHERE
  c."CompanyId" = ?
  AND c."ImportDate" = ?
  AND c."LifeId" = ?
  AND c."CarrierId" = ?
  AND c."PlanTypeId" = ?
  AND c."PlanId" = ?
  AND c."Code" = 'NEW'