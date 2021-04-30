insert into "ReportTransamericaEligibility" ("CompanyId", "ImportDate", "ImportDataId", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId", "EmployeeNumber", "ProductType", "Option", "Tier", "RelationshipSSN", "RelationshipEID")
select
  "ImportData"."CompanyId"
  , "ImportData"."ImportDate"
  , "ImportData"."Id" as "ImportDataId"
  , "LifeData"."LifeId"
  , "WashedData"."CarrierId"
  , "WashedData"."PlanTypeId"
  , "WashedData"."PlanId"
  , "WashedData"."CoverageTierId"
  , "ImportData"."EmployeeId" as "EmployeeNumber"
  , "CompanyPlanType"."PlanTypeCode" as "ProductType"
  , "CompanyPlan"."PlanNormalized" as "Option"
  , "CompanyCoverageTier"."CoverageTierNormalized" as "Tier"
  , coalesce ( "ImportData"."EmployeeSSN", "ImportData"."EmployeeId" ) as "RelationshipSSN"  -- Assumes EmployeeSSN is the field that will hold the relationship between employee and dependents
  , coalesce ( "ImportData"."EmployeeId", "ImportData"."EmployeeSSN" ) as "RelationshipEID"  -- Assumes EmployeeId is the field that will hold the relationship between employee and dependents
from
  "ImportData"
  join "WashedData" on (  "WashedData"."CompanyId" = "ImportData"."CompanyId" and "WashedData"."ImportDate" = "ImportData"."ImportDate" and "ImportData"."Id" = "WashedData"."ImportDataId" )
  join "LifeData" on ( "LifeData"."CompanyId" = "ImportData"."CompanyId" and "LifeData"."ImportDate" = "ImportData"."ImportDate" and "LifeData"."ImportDataId" = "ImportData"."Id" )
  join "CompanyCarrier" on ( "CompanyCarrier"."Id" = "WashedData"."CarrierId" )
  join "CompanyPlanType" on ( "CompanyPlanType"."Id" = "WashedData"."PlanTypeId" )
  join "CompanyPlan" on ( "CompanyPlan"."Id" = "WashedData"."PlanId" )
  join "CompanyCoverageTier" on ( "CompanyCoverageTier"."Id" = "WashedData"."CoverageTierId" )
  left join "ReportTransamericaEligibility" on (
    "ReportTransamericaEligibility"."CompanyId" = "ImportData"."CompanyId"
    and "ReportTransamericaEligibility"."ImportDate" = "ImportData"."ImportDate"
    and "ReportTransamericaEligibility"."ImportDataId" = "ImportData"."Id"
  )
where
  "ImportData"."CompanyId" = ?
  and "ImportData"."ImportDate" = ?
  and "WashedData"."CarrierId" = ?
  and "CompanyPlan"."PremiumEquivalent" = false
  and "ImportData"."CoverageStartDate" < "ImportData"."ImportDate" + interval '+1 month'
  and "ReportTransamericaEligibility"."Id" is null