update "ReportTransamericaEligibility" set
    "RelationshipId" = "RelationshipSSN"
where
    "CompanyId" = ?
    and "ImportDate" = ?
    and "CarrierId" = ?
    and "RelationshipId" is null