-- We will cross-check the dependent records on the file with the tier being used.
-- ES – we only send the employee record and the spouse dependent record, regardless of other child dependents in
update
  "ReportTransamericaEligibilityDetails"
SET
  "IssueCode" = 'TIER_ES_IGNORE'
WHERE
  "CompanyId" = ?
  and "ImportDate" = ?
  and "Tier" = 'ES'
  and "RelationshipCode" = 'dependent'