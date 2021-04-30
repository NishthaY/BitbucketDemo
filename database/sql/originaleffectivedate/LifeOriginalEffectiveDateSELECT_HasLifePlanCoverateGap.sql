select
  CASE WHEN "LostDate" < ? THEN true ELSE FALSE END as "HasGapInCoverage"
from
  "LifeOriginalEffectiveDate"
where
  "LifeId" = ?
  and "CarrierId" = ?
  and "PlanTypeId" = ?
  and "PlanId" = ?
  and "LostDate" is not null
order by "LostDate" DESC
limit 1