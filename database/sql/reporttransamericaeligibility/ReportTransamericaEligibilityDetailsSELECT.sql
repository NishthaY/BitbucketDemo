select * from "ReportTransamericaEligibilityDetails"
where "CompanyId" = ?
      and "ImportDate" = ?
      and "CarrierId" = ?
      and "PlanTypeId" = ?
      and "PlanId" = ?
      and "CoverageTierId" = ?
      and "RelationshipId" = ?
      and ( "IssueCode" is null OR "IssueCode" not in ( 'TIER_EC_IGNORE', 'TIER_EO_IGNORE', 'TIER_ES_IGNORE', 'TIER_CHANGE_IGNORE' ) )
order by "SortId" asc