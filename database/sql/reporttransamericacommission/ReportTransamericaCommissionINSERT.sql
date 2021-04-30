insert into "ReportTransamericaCommission" ( "CompanyId", "ImportDate", "ImportDataId", "MasterPolicy", "EmployeeNumber", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId", "ProductType", "Option", "Tier", "LostLife")
SELECT
  "ImportData"."CompanyId"
  , "ImportData"."ImportDate"
  , "ImportData"."Id" as "ImportDataId"
  , "ImportData"."Policy" as "MasterPolicy"
  , "ImportData"."EmployeeId" as "EmployeeNumber"
  , "LifeData"."LifeId"
  , "WashedData"."CarrierId"
  , "WashedData"."PlanTypeId"
  , "WashedData"."PlanId"
  , "WashedData"."CoverageTierId"
  , "CompanyPlanType"."PlanTypeCode" as "ProductType"
  , "CompanyPlan"."PlanNormalized" as "Option"
  , "CompanyCoverageTier"."CoverageTierNormalized" as "Tier"
  , false as "LostLife"
from
  "ImportData"
  join "LifeData" on ( "LifeData"."CompanyId" = "ImportData"."CompanyId" and "LifeData"."ImportDate" = "ImportData"."ImportDate" and "LifeData"."ImportDataId" = "ImportData"."Id")
  join "WashedData" on ( "WashedData"."CompanyId" = "ImportData"."CompanyId" and "WashedData"."ImportDate" = "ImportData"."ImportDate" and "ImportData"."Id" = "WashedData"."ImportDataId")
  join "RelationshipData" on (
    "RelationshipData"."CompanyId" = "ImportData"."CompanyId"
    and "RelationshipData"."ImportDate" = "ImportData"."ImportDate"
    and "RelationshipData"."ImportDataId" = "ImportData"."Id"
    and "RelationshipData"."RelationshipCode" = 'employee'
  )
  join "CompanyCarrier" on ( "CompanyCarrier"."Id" = "WashedData"."CarrierId" )
  join "CompanyPlanType" on ( "WashedData"."CompanyId" = "ImportData"."CompanyId" and "WashedData"."ImportDate" = "ImportData"."ImportDate" and "CompanyPlanType"."Id" = "WashedData"."PlanTypeId" )
  join "CompanyPlan" on ( "CompanyPlan"."Id" = "WashedData"."PlanId" )
  join "CompanyCoverageTier" on ( "CompanyCoverageTier"."Id" = "WashedData"."CoverageTierId" )
  left join "ReportTransamericaCommission" on ( "ReportTransamericaCommission"."CompanyId" = "ImportData"."CompanyId" and "ReportTransamericaCommission"."ImportDate" = "ImportData"."ImportDate" and "ReportTransamericaCommission"."ImportDataId" = "ImportData"."Id" )
where
  "ImportData"."CompanyId" = ?
  and "ImportData"."ImportDate" = ?
  and "CompanyCarrier"."Id" = ?
  and "ReportTransamericaCommission"."Id" is null
