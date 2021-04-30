-- All items identified in the worker are items that need to have
-- their issue code set to TIER_CHANGE_IGNORE.  Set that now.
update "ReportTransamericaEligibilityDetails"
    set "IssueCode" = 'TIER_CHANGE_IGNORE'
from "ReportTransamericaEligibilityDetailsWorker"
where
    "ReportTransamericaEligibilityDetails"."CompanyId" = ?
    and "ReportTransamericaEligibilityDetails"."ImportDate" = ?
    and "ReportTransamericaEligibilityDetails"."CompanyId" = "ReportTransamericaEligibilityDetailsWorker"."CompanyId"
    and "ReportTransamericaEligibilityDetails"."ImportDate" = "ReportTransamericaEligibilityDetailsWorker"."ImportDate"
    and "ReportTransamericaEligibilityDetails"."EmployeeNumber" = "ReportTransamericaEligibilityDetailsWorker"."EmployeeNumber"
    and "ReportTransamericaEligibilityDetails"."CarrierId" = "ReportTransamericaEligibilityDetailsWorker"."CarrierId"
    and "ReportTransamericaEligibilityDetails"."PlanTypeId" = "ReportTransamericaEligibilityDetailsWorker"."PlanTypeId"
    and "ReportTransamericaEligibilityDetails"."PlanId" = "ReportTransamericaEligibilityDetailsWorker"."PlanId"
    and "ReportTransamericaEligibilityDetails"."CoverageTierId" = "ReportTransamericaEligibilityDetailsWorker"."CoverageTierId"
    and "ReportTransamericaEligibilityDetails"."LifeId" = "ReportTransamericaEligibilityDetailsWorker"."LifeId"
    and "ReportTransamericaEligibilityDetailsWorker"."Ignore" = false