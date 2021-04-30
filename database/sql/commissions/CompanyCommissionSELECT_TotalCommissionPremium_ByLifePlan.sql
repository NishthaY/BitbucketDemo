select
    sum("CommissionablePremium") as "TotalCommissionablePremium"
from "CompanyCommission"
WHERE
    "CompanyId" = ?
    and "ImportDate" = ?::DATE + interval '{OFFSET} month'
    and "LifeId" = ?
    and "CarrierId" = ?
    and "PlanTypeId" = ?
    and "PlanId" = ?
group by
    "CompanyId", "LifeId", "CarrierId", "PlanTypeId", "PlanId"