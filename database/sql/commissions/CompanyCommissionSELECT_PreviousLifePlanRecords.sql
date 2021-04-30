select
  "CompanyId"
  , "ImportDate"
  , "LifeId"
  , "CarrierId"
  , "PlanTypeId"
  , "PlanId"
  , "CommissionEffectiveDate"
  , "CommissionablePremium"
  , "ResetRecord"
from
  "CompanyCommission"
where
  "CompanyId" = ?
  and "ImportDate" = ?::DATE + interval '{OFFSET} month'
  and "LifeId" = ?
  and "CarrierId" = ?
  and "PlanTypeId" = ?
  and "PlanId" = ?
order by "CommissionEffectiveDate" desc, "Id" desc

