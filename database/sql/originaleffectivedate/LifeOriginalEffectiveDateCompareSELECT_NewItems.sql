-- identify
-- Find all NEW items this month in the compare table.
select
  "LifeId"
  , "CarrierId"
  , "PlanTypeId"
  , "PlanId"
from
  "LifeOriginalEffectiveDateCompare" c
WHERE
  c."CompanyId" = ?
  and c."ImportDate" = ?
  and c."Code" = 'NEW'
group by
  "LifeId", "CarrierId", "PlanTypeId", "PlanId"
