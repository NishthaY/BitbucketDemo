-- We will cross-check the dependent records on the file with the tier being used.
-- EC – we only send the employee record and the child dependent records, regardless of spouse in the input file.
update
  "ReportTransamericaEligibilityDetails"
SET
  "IssueCode" = 'TIER_EC_IGNORE'
WHERE
  "CompanyId" = ?
  and "ImportDate" = ?
  and "Tier" = 'EC'
  and "RelationshipCode" = 'spouse'