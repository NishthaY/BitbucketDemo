-- We will cross-check the dependent records on the file with the tier being used.
-- EO – we only send the employee record, regardless of dependents in the input file.

update
  "ReportTransamericaEligibilityDetails"
SET
  "IssueCode" = 'TIER_EO_IGNORE'
WHERE
  "CompanyId" = ?
  and "ImportDate" = ?
  and "Tier" = 'EO'
  and "RelationshipCode" <> 'employee'