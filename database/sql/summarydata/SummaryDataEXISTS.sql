select
    CASE WHEN count(*) = 0 THEN false ELSE true END as "Exists"
from
    "SummaryData"
where
    "CompanyId" = ?
    and "ImportDate" = ?
    and "CarrierId" = ?
    and "PlanTypeId" {PLANTYPEID}
    and "PlanId" {PLANID}
    and "CoverageTierId" {COVERAGETIERID}
    and "TobaccoUser" {TOBACCOUSER}
    and "AgeBandId" {AGEBAND}
