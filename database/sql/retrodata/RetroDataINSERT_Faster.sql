insert into "RetroData" ( "CompanyId", "ImportDataId", "ImportDate", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId", "CoverageStartDate", "CoverageEndDate", "LifeId", "MonthlyCost", "Volume", "PlanTypeCode")
select
		"ImportData"."CompanyId" as "CompanyId"
	, "ImportData"."Id" as "ImportDataId"
	, "ImportData"."ImportDate"
	, "CompanyCarrier"."Id" as "CarrierId"
	, "CompanyPlanType"."Id" as "PlanTypeId"
	, "CompanyPlan"."Id" as "PlanId"
	, "CompanyCoverageTier"."Id" as "CoverageTierId"
	, "ImportData"."CoverageStartDate" as "CoverageStartDate"
	, "ImportData"."CoverageEndDate" as "CoverageEndDate"
	, "LifeData"."LifeId"
	, coalesce("RelationshipData"."MonthlyCost", "ImportData"."MonthlyCost") as "MonthlyCost"
	, coalesce("RelationshipData"."Volume", "ImportData"."Volume") as "Volume"
	, "CompanyPlanType"."PlanTypeCode" as "PlanTypeCode"

from
	"ImportData"
	join "LifeData" on ( "LifeData"."ImportDataId" = "ImportData"."Id")
	join "CompanyLife" on ( "CompanyLife"."Id" = "LifeData"."LifeId" AND "CompanyLife"."Enabled" = true ) -- BAH: Must exclude lives that are not enabled.
	join "CompanyCarrier" on ( "CompanyCarrier"."CompanyId" = "ImportData"."CompanyId" and "CompanyCarrier"."CarrierNormalized" = upper("ImportData"."Carrier") )
	join "CompanyPlanType" on ( "CompanyPlanType"."CarrierId" =  "CompanyCarrier"."Id" and "CompanyPlanType"."PlanTypeNormalized" = upper("ImportData"."PlanType") )
	join "CompanyPlan" on ( "CompanyPlan"."CarrierId" =  "CompanyCarrier"."Id" and "CompanyPlan"."PlanTypeId" = "CompanyPlanType"."Id" and "CompanyPlan"."PlanNormalized" = upper("ImportData"."Plan") )
	join "CompanyCoverageTier" on ( "CompanyCoverageTier"."CarrierId" =  "CompanyCarrier"."Id" and "CompanyCoverageTier"."PlanTypeId" = "CompanyPlanType"."Id" and "CompanyCoverageTier"."PlanId" = "CompanyPlan"."Id" and "CompanyCoverageTier"."CoverageTierNormalized" = upper("ImportData"."CoverageTier") )
	left join "RelationshipData"  on ( "RelationshipData"."ImportDataId" = "ImportData"."Id")
	left join "RetroData" on ( "RetroData"."ImportDataId" = "ImportData"."Id" )

where
	"ImportData"."CompanyId" = ?
	and "ImportData"."ImportDate" = ?
	and "CompanyPlanType"."Ignored" = false
	and "CompanyPlanType"."PlanTypeCode" is not null
	and "RetroData"."Id" is null