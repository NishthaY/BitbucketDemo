-- We are trying to get child records to hide from the report if they have a termination
-- record in the past and the parent changed tiers.

-- We already know the worker is filled with children that have a termination date in the
-- past.

-- Look to the parent for each of these child records. If the parent is not terminating then
-- this is the case were we want to suppress this life.  Since the worker table holds only
-- the things we want filter out, throw these out by setting ignore to true.


update "ReportTransamericaEligibilityDetailsWorker"
    set "Ignore" = true
from "ReportTransamericaEligibilityDetails"
where
    "ReportTransamericaEligibilityDetailsWorker"."CompanyId" = ?
    and "ReportTransamericaEligibilityDetailsWorker"."ImportDate" = ?
    and "ReportTransamericaEligibilityDetailsWorker"."CompanyId" = "ReportTransamericaEligibilityDetails"."CompanyId"
    and "ReportTransamericaEligibilityDetailsWorker"."ImportDate" = "ReportTransamericaEligibilityDetails"."ImportDate"
    and "ReportTransamericaEligibilityDetailsWorker"."EmployeeNumber" = "ReportTransamericaEligibilityDetails"."EmployeeNumber"
    and "ReportTransamericaEligibilityDetailsWorker"."CarrierId" = "ReportTransamericaEligibilityDetails"."CarrierId"
    and "ReportTransamericaEligibilityDetailsWorker"."PlanTypeId" = "ReportTransamericaEligibilityDetails"."PlanTypeId"
    and "ReportTransamericaEligibilityDetailsWorker"."PlanId" = "ReportTransamericaEligibilityDetails"."PlanId"
    and "ReportTransamericaEligibilityDetailsWorker"."CoverageTierId" = "ReportTransamericaEligibilityDetails"."CoverageTierId"
    and "ReportTransamericaEligibilityDetailsWorker"."Ignore" = false
    and "ReportTransamericaEligibilityDetails"."RelationshipCode" = 'employee'
    and "ReportTransamericaEligibilityDetails"."TerminationDate" is not null
