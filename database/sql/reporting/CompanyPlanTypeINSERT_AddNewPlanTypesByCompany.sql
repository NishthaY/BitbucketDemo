insert into "CompanyPlanType" ( "CompanyId", "CarrierId", "PlanTypeNormalized" )
select
	"ImportData"."CompanyId"
	, "CompanyCarrier"."Id" as "CarrierId"
	, upper("ImportData"."PlanType") as "PlanTypeNormalized"
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
where
	"ImportData"."CompanyId" = ?
	and "ImportData"."Finalized" = false
	and "CompanyPlanType"."PlanTypeNormalized" is null
group by "ImportData"."CompanyId", "CompanyCarrier"."Id", upper("ImportData"."PlanType")
