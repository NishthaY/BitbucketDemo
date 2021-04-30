insert into "CompanyCommission" ( "CompanyId", "ImportDate", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "CommissionEffectiveDate", "CommissionablePremium", "ResetRecord" )
select
    "CompanyId"
     , ? as "ImportDate"
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
