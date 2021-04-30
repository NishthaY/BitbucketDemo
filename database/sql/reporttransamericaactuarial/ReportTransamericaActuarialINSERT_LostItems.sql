insert into "ReportTransamericaActuarial" ("CompanyId", "ImportDate", "ImportDataId", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId", "EmployeeNumber", "ProductType", "Option", "Tier", "LostItem")
select
  last_month."CompanyId"
  , to_char(last_month."ImportDate" + interval '+1 month', 'MM/DD/YY')::date as "ImportDate"
  , last_month."Id" as "ImportDateId"
  , "LifeData"."LifeId"
  , "WashedData"."CarrierId"
  , "WashedData"."PlanTypeId"
  , "WashedData"."PlanId"
  , "WashedData"."CoverageTierId"
  , last_month."EmployeeId" as "EmployeeNumber"
  , "CompanyPlanType"."PlanTypeCode" as "ProductType"
  , "CompanyPlan"."PlanNormalized" as "Option"
  , "CompanyCoverageTier"."CoverageTierNormalized" as "Tier"
  , true as "LostItem"
from
  "ImportData" last_month
  join "LifeData" on ( "LifeData"."ImportDataId" = last_month."Id" )
  join "CompanyLife" on ( "CompanyLife"."CompanyId" = last_month."CompanyId" and "CompanyLife"."Id" = "LifeData"."LifeId" and "CompanyLife"."Enabled" = true )
  join "WashedData" on ( "WashedData"."ImportDataId" = last_month."Id" )
  join "CompanyCarrier" on ( "CompanyCarrier"."Id" = "WashedData"."CarrierId" )
  join "CompanyPlanType" on ( "CompanyPlanType"."Id" = "WashedData"."PlanTypeId" )
  join "CompanyPlan" on ( "CompanyPlan"."Id" = "WashedData"."PlanId" )
  join "CompanyCoverageTier" on ( "CompanyCoverageTier"."Id" = "WashedData"."CoverageTierId" )
  left join "ReportTransamericaActuarial" this_month on
                                             (
                                               this_month."CompanyId" = last_month."CompanyId"
                                               and this_month."ImportDate" = ?
                                               and this_month."EmployeeNumber" = last_month."EmployeeId"
                                               and this_month."LifeId" = "LifeData"."LifeId"
                                               and this_month."CarrierId" = "WashedData"."CarrierId"
                                               and this_month."PlanTypeId" = "WashedData"."PlanTypeId"
                                               and this_month."PlanId" = "WashedData"."PlanId"
                                               and this_month."CoverageTierId" = "WashedData"."CoverageTierId"
                                               )
where
  last_month."CompanyId" = ?
  and last_month."ImportDate" = ?
  and "WashedData"."CarrierId" = ?
  and "CompanyPlan"."PremiumEquivalent" = false
  and last_month."CoverageStartDate" < last_month."ImportDate" + interval '+1 month'
  and this_month."Id" is null