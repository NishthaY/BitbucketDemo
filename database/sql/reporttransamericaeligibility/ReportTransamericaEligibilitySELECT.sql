select
  "CompanyId"
  , "ImportDate"
  , "CarrierId"
  , "PlanTypeId"
  , "PlanId"
  , "CoverageTierId"
  , "RelationshipId"
  , "ProductType"
  , "Option"
  , "Tier"
from
  "ReportTransamericaEligibility"
where "CompanyId" = ?
      and "ImportDate" = ?
      and "IssueCode" is null
group BY
  "CompanyId", "ImportDate", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId", "RelationshipId", "ProductType", "Option", "Tier"