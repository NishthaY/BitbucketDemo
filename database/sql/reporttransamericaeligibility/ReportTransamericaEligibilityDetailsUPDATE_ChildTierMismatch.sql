-- Look for children associated with a parent that do not have a matching
-- Coverage Tier Code.  These are invalid records and need to produce a warning.
-- These lives will be excluded from some reports.

with t as (
    SELECT
      child."Id" as "Id"
    FROM
      "ReportTransamericaEligibilityDetails" child
      left join "ReportTransamericaEligibilityDetails" parent on
                                                                (
                                                                  parent."CompanyId" = child."CompanyId"
                                                                  and parent."ImportDate" = child."ImportDate"
                                                                  and parent."RelationshipCode" = 'employee'
                                                                  --and parent."EmployeeNumber" = child."EmployeeNumber"
                                                                  and parent."RelationshipId" = child."RelationshipId"
                                                                  and parent."CarrierId" = child."CarrierId"
                                                                  and parent."PlanTypeId" = child."PlanTypeId"
                                                                  and parent."PlanId" = child."PlanId"
                                                                  and parent."CoverageTierId" = child."CoverageTierId"
                                                                  )
    WHERE
      child."CompanyId" = ?
      and child."ImportDate" = ?
      and child."RelationshipCode" <> 'employee'
      and parent."Id" is null
)
update "ReportTransamericaEligibilityDetails" d set "IssueCode" = 'CHILD_TIER_MISMATCH' from t where t."Id" = d."Id"