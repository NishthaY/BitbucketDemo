update "CompanyCommission"
set "ResetRecord" = false
where
    "CompanyId" = ?
    and "ImportDate" = ?
    and "LifeId" = ?
    and "CarrierId" = ?
    and "PlanTypeId" = ?
    and "PlanId" = ?