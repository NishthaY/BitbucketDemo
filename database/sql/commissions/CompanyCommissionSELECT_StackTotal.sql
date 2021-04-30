select sum("CommissionablePremium") as "StackTotal" from "CompanyCommission" cc
where
  cc."CompanyId" = ?
  and cc."ImportDate" = ?
  and cc."LifeId" = ?
  and cc."CarrierId" = ?
  and cc."PlanTypeId" = ?
  and cc."PlanId" = ?