-- Given a life/plan, is there any records in the table that do not
-- have a LostDate.
select
  case WHEN count(*) = 0 then FALSE else TRUE end as "HasActiveLifePlan"
from
  "LifeOriginalEffectiveDate"
where
  "LifeId" = ?
  and "CarrierId" = ?
  and "PlanTypeId" = ?
  and "PlanId" = ?
  and "LostDate" is null