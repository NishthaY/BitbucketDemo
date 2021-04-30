insert into "CompanyCommissionValidate" ( "CompanyId", "ImportDate", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "CommissionablePremium" )
select
  cc."CompanyId"
  , cc."ImportDate"
  , cc."LifeId"
  , cc."CarrierId"
  , cc."PlanTypeId"
  , cc."PlanId"
  , sum("CommissionablePremium") as "CommissionablePremium"
from
  "CompanyCommission" cc
WHERE
  cc."CompanyId" = ?
  and cc."ImportDate" = ?
GROUP BY
  cc."CompanyId", cc."ImportDate", cc."LifeId", cc."CarrierId", cc."PlanTypeId", cc."PlanId"