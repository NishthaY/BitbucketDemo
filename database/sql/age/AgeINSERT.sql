insert into "Age" ( "CompanyId", "ImportDataId", "ImportDate", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId", "Age", "AgeTypeId", "AgeDescription", "AgeOn", "WashRule", "DateOfBirth" )
select
	"ImportData"."CompanyId" as "CompanyId"
	, "ImportData"."Id" as "ImportDataId"
	, "ImportData"."ImportDate"
	, "CompanyCarrier"."Id" as "CarrierId"
	, "CompanyPlanType"."Id" as "PlanTypeId"
	, "CompanyPlan"."Id" as "PlanId"
	, "CompanyCoverageTier"."Id" as "CoverageTierId"
	, null as "Age"
	, 3 as "AgeTypeId"
	, null as "AgeDescription"
	, null as "AgeOn"
	, "CompanyPlanType"."WashRule"::int as "WashRule"
	, "ImportData"."DateOfBirth" as "DateOfBirth"
from
	"ImportData"
	join "CompanyCarrier" on
	(
		"CompanyCarrier"."CompanyId" = "ImportData"."CompanyId"
		and "CompanyCarrier"."CarrierNormalized" = upper("ImportData"."Carrier")
	)
	join "CompanyPlanType" on
 	(
 		"CompanyPlanType"."CarrierId" =  "CompanyCarrier"."Id"
 		and "CompanyPlanType"."PlanTypeNormalized" = upper("ImportData"."PlanType")
 	)
	join "CompanyPlan" on
 	(
		"CompanyPlan"."CarrierId" =  "CompanyCarrier"."Id"
		and "CompanyPlan"."PlanTypeId" = "CompanyPlanType"."Id"
 		and "CompanyPlan"."PlanNormalized" = upper("ImportData"."Plan")
 	)
	join "CompanyCoverageTier" on
	(
		"CompanyCoverageTier"."CarrierId" =  "CompanyCarrier"."Id"
		and "CompanyCoverageTier"."PlanTypeId" = "CompanyPlanType"."Id"
		and "CompanyCoverageTier"."PlanId" = "CompanyPlan"."Id"
		and "CompanyCoverageTier"."CoverageTierNormalized" = upper("ImportData"."CoverageTier")
	)
where
	"ImportData"."CompanyId" = ?
	and "ImportDate" = ?
	and "ImportData"."Finalized" = false
	and "CompanyPlanType"."Ignored" = false
	and "CompanyPlanType"."PlanTypeCode" is not null
