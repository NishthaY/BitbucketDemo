insert into "CompanyPlan" ( "CompanyId", "CarrierId", "PlanTypeId", "PlanNormalized")
select
	"ImportData"."CompanyId" as "CompanyId"
	, "CompanyCarrier"."Id" as "CarrierId"
	, "CompanyPlanType"."Id" as "PlanTypeId"
	, upper("ImportData"."Plan") as "PlanNormalized"
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
where
	"ImportData"."CompanyId" = ?
	and "ImportData"."Finalized" = false
	and "CompanyPlan"."PlanNormalized" is null
group by "ImportData"."CompanyId" , "CompanyCarrier"."Id" , "CompanyPlanType"."Id", upper("ImportData"."Plan")
