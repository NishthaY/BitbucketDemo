select
  "Id"
  , "CompanyId"
  , "ImportDate"
  , "LifeId"
  , "CarrierId"
  , "PlanTypeId"
  , "PlanId"
  , "CommissionEffectiveDate"
  , "CommissionablePremium"
from
  "CompanyCommission"
where
  "CompanyId" = ?
  and "ImportDate" = ?
  and "LifeId" = ?
  and "CarrierId" = ?
  and "PlanTypeId" = ?
  and "PlanId" = ?
order by "CommissionEffectiveDate" {ORDER}, "Id" asc

