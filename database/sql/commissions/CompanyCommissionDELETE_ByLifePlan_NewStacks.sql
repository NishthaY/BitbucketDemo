delete from "CompanyCommission" c
where
  c."CompanyId" = ?
  and c."ImportDate" = ?
  and c."LifeId" = ?
  and c."CarrierId" = ?
  and c."PlanTypeId" = ?
  and c."PlanId" = ?
  and c."CommissionEffectiveDate" > ?