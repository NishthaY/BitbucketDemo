select
       *
from
     "CompanyCommission"
where
    "CompanyId" = ?
    and "ImportDate" = ?
    and "LifeId" = ?
    and "CarrierId" = ?
    and "PlanTypeId" = ?
    and "PlanId" = ?
order by "CommissionEffectiveDate" asc
limit 1