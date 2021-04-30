insert into "ReportReviewWarnings" ( "CompanyId", "ImportDataId", "ImportDate", "Issue" )
select
  details."CompanyId"
  , details."ImportDataId"
  , details."ImportDate"
  , CASE
    WHEN details."IssueCode" = 'TIER_ES_IGNORE' THEN 'This record has been removed from the eligibility report because it has a tier of ES and dependent records are not shown for this tier.'
    WHEN details."IssueCode" = 'TIER_EO_IGNORE' THEN 'This record has been removed from the eligibility report because it has a tier of EO and spouse/dependent records are not shown for this tier.'
    WHEN details."IssueCode" = 'TIER_EC_IGNORE' THEN 'This record has been removed from the eligibility report because it has a tier of EC and spouse records are not shown for this tier.'
    WHEN details."IssueCode" = 'TIER_CHANGE_IGNORE' THEN 'Tier change detected on eligibility report where dependent terminated before import date.  Life suppressed on new tier record.'
    WHEN details."IssueCode" = 'CHILD_TIER_MISMATCH' THEN 'Multiple tiers have been found within the associated family unit.  This record will assume a different tier matching the employee in the eligibility report.'
    END
from
  "ReportTransamericaEligibilityDetails" details
  join "ImportData" on ( details."ImportDataId" = "ImportData"."Id" )
WHERE
  details."CompanyId" = ?
  and details."ImportDate" = ?
  and details."IssueCode" is not null

