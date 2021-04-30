-- Capture dependents terminating before the current import date where their
-- corresponding parent on the same tier is active.
insert into "ReportTransamericaEligibilityDetailsWorker" ("CompanyId", "ImportDate", "EmployeeNumber", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId", "RelationshipCode")
select
    d."CompanyId"
    ,d."ImportDate"
    ,d."EmployeeNumber"
    ,d."LifeId"
    ,d."CarrierId"
    ,d."PlanTypeId"
    ,d."PlanId"
    ,d."CoverageTierId"
    ,d."RelationshipCode"
from
    "ReportTransamericaEligibilityDetails" d
WHERE
    d."CompanyId" = ?
    and d."ImportDate" = ?
    and d."RelationshipCode" <> 'employee'
    and d."Status" = 'I'
    and d."TerminationDate" < d."ImportDate"        -- Dependent is terminating in the past.