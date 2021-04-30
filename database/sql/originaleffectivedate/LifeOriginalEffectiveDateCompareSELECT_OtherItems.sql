select
  compare."LifeId"
  , compare."CarrierId"
  , compare."PlanTypeId"
  , compare."PlanId"
from
  "LifeOriginalEffectiveDateCompare" compare
where
  compare."CompanyId" = ?
  and compare."ImportDate" = ?
  and compare."Code" <> 'NEW'
  and compare."OldestLifePlanEffectiveDate" is null