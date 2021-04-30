insert into "CompanyCoverageTier" ( "CompanyId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierNormalized")
select
	"ImportData"."CompanyId"
	, "CompanyCarrier"."Id" as "CarrierId"
	, "CompanyPlanType"."Id" as "PlanTypeId"
	, "CompanyPlan"."Id" as "PlanId"
	, upper("ImportData"."CoverageTier") as "CoverageTier"
from
		"ImportData"
	join "CompanyCarrier" on
	(
		"CompanyCarrier"."CompanyId" = "ImportData"."CompanyId"
		and "CompanyCarrier"."CarrierNormalized" = upper("ImportData"."Carrier")
	)
	left join "CompanyPlanType" on
 	(
 		"CompanyPlanType"."CarrierId" =  "CompanyCarrier"."Id"
 		and "CompanyPlanType"."PlanTypeNormalized" = upper("ImportData"."PlanType")
 	)
	left join "CompanyPlan" on
 	(
		"CompanyPlan"."CarrierId" =  "CompanyCarrier"."Id"
		and "CompanyPlan"."PlanTypeId" = "CompanyPlanType"."Id"
 		and "CompanyPlan"."PlanNormalized" = upper("ImportData"."Plan")
 	)
	left join "CompanyCoverageTier" on
	(
		"CompanyCoverageTier"."CarrierId" =  "CompanyCarrier"."Id"
		and "CompanyCoverageTier"."PlanTypeId" = "CompanyPlanType"."Id"
		and "CompanyCoverageTier"."PlanId" = "CompanyPlan"."Id"
		and "CompanyCoverageTier"."CoverageTierNormalized" = upper("ImportData"."CoverageTier")
	)
where
	"ImportData"."CompanyId" = ?
	and "ImportData"."Finalized" = false
	and "CompanyCoverageTier"."CoverageTierNormalized" is null
group by "ImportData"."CompanyId" , "CompanyCarrier"."Id" , "CompanyPlanType"."Id", "CompanyPlan"."Id", upper("ImportData"."CoverageTier")
