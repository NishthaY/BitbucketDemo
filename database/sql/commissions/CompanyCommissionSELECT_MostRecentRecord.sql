select * from "CompanyCommission" cc
where
    cc."CompanyId" = ?
    and cc."ImportDate" = ?
    and cc."LifeId" = ?
    and cc."CarrierId" = ?
    and cc."PlanTypeId" = ?
    and cc."PlanId" = ?
order by "CommissionEffectiveDate" desc
limit 1