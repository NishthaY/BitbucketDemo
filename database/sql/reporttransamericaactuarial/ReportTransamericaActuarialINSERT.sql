insert into "ReportTransamericaActuarial" ("CompanyId", "ImportDate", "ImportDataId", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId", "EmployeeNumber", "ProductType", "Option", "Tier")

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
from
  "ImportData"
  join "WashedData" on (  "WashedData"."CompanyId" = "ImportData"."CompanyId" and   "WashedData"."ImportDate" = "ImportData"."ImportDate" and "WashedData"."ImportDataId" = "ImportData"."Id" )
  join "LifeData" on ( "LifeData"."CompanyId" = "ImportData"."CompanyId" and "LifeData"."ImportDate" = "ImportData"."ImportDate" and "LifeData"."ImportDataId" = "ImportData"."Id" )
  join "CompanyCarrier" on ( "CompanyCarrier"."Id" = "WashedData"."CarrierId" )
  join "CompanyPlanType" on ( "CompanyPlanType"."Id" = "WashedData"."PlanTypeId" )
  join "CompanyPlan" on ( "CompanyPlan"."Id" = "WashedData"."PlanId" )
  join "CompanyCoverageTier" on ( "CompanyCoverageTier"."Id" = "WashedData"."CoverageTierId" )
  left join "ReportTransamericaActuarial" on (
    "ReportTransamericaActuarial"."CompanyId" = "ImportData"."CompanyId"
    and "ReportTransamericaActuarial"."ImportDate" = "ImportData"."ImportDate"
    and "ReportTransamericaActuarial"."ImportDataId" = "ImportData"."Id"
  )
where
  "ImportData"."CompanyId" = ?
  and "ImportData"."ImportDate" = ?
  and "WashedData"."CarrierId" = ?
  and "CompanyPlan"."PremiumEquivalent" = false
  and "ImportData"."CoverageStartDate" < "ImportData"."ImportDate" + interval '+1 month'
  and "ReportTransamericaActuarial"."Id" is null

