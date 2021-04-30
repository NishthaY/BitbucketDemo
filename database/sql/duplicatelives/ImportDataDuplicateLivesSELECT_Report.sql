select
  "ImportDataDuplicateLives"."ImportDate"
  , "ImportDataDuplicateLives"."EmployeeId"
  , "ImportDataDuplicateLives"."SSN"
  , "ImportDataDuplicateLives"."FirstName"
  , "ImportDataDuplicateLives"."DateOfBirth"
  , "ImportDataDuplicateLives"."Relationship"
  , "CompanyCarrier"."UserDescription" as "Carrier"
  , "CompanyPlanType"."UserDescription" as "PlanType"
  , "CompanyPlan"."UserDescription" as "Plan"
  , "CompanyCoverageTier"."UserDescription" as "CoverageTier"
  , format('This life, with the same coverage, appears %s times in the import file.', "ImportDataDuplicateLives"."Count") as "Reason"
from
  "ImportDataDuplicateLives"
  join "CompanyCarrier" on ( "ImportDataDuplicateLives"."CarrierId" = "CompanyCarrier"."Id")
  join "CompanyPlanType" on ( "ImportDataDuplicateLives"."PlanTypeId" = "CompanyPlanType"."Id")
  join "CompanyPlan" on ( "ImportDataDuplicateLives"."PlanId" = "CompanyPlan"."Id")
  join "CompanyCoverageTier" on ( "ImportDataDuplicateLives"."CoverageTierId" = "CompanyCoverageTier"."Id")
WHERE
  "ImportDataDuplicateLives"."CompanyId" = ?
  and "ImportDataDuplicateLives"."ImportDate" = ?