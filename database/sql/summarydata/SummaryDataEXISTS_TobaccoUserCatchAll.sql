select
    CASE WHEN count(*) = 0 THEN false ELSE true END as "Exists"
from
    "SummaryData"
where
    "CompanyId" = ?
    and "ImportDate" = ?
    and "CarrierId" = ?
    and "PlanTypeId" = ?
    and "PlanId" = ?
    and "CoverageTierId" = ?
    and ("AgeBandId" is null OR "AgeBandId" = ?)
    and "TobaccoUser" is null
