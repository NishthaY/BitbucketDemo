-- At this point we have identified items we need to flag as IGNORE_TIER_CHANGE.
-- The last thing we want to do is check the LostLife flag.  If the life was lost, the
-- software generated the termination.  In this case, let this record slip through
-- onto the report by setting the Ignore flag to true.
update "ReportTransamericaEligibilityDetailsWorker"
    set "Ignore" = true
from "ReportTransamericaEligibility"
where
    "ReportTransamericaEligibilityDetailsWorker"."CompanyId" = ?
    and "ReportTransamericaEligibilityDetailsWorker"."ImportDate" = ?
    and "ReportTransamericaEligibilityDetailsWorker"."CompanyId" = "ReportTransamericaEligibility"."CompanyId"
    and "ReportTransamericaEligibilityDetailsWorker"."ImportDate" = "ReportTransamericaEligibility"."ImportDate"
    and "ReportTransamericaEligibilityDetailsWorker"."LifeId" = "ReportTransamericaEligibility"."LifeId"
    and "ReportTransamericaEligibilityDetailsWorker"."CarrierId" = "ReportTransamericaEligibility"."CarrierId"
    and "ReportTransamericaEligibilityDetailsWorker"."PlanTypeId" = "ReportTransamericaEligibility"."PlanTypeId"
    and "ReportTransamericaEligibilityDetailsWorker"."PlanId" = "ReportTransamericaEligibility"."PlanId"
    and "ReportTransamericaEligibilityDetailsWorker"."CoverageTierId" = "ReportTransamericaEligibility"."CoverageTierId"
    and "ReportTransamericaEligibilityDetailsWorker"."Ignore" = false
    and "ReportTransamericaEligibility"."LostItem" = true