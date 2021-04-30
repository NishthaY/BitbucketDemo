update "ReportTransamericaEligibility" set "IssueCode" = 'NO_DETAILS' where
    "CompanyId" = ?
    and "ImportDate" = ?
    and "CarrierId" = ?
    and "PlanTypeId" = ?
    and "PlanId" = ?
    and "CoverageTierId" = ?
    and "RelationshipId" = ?