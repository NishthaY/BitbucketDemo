select
	"CompanyPlan"."Id"
	, "CompanyCarrier"."CarrierNormalized"
	, "CompanyPlanType"."PlanTypeNormalized"
	, "CompanyPlan"."PlanNormalized"
	, "CompanyPlan"."UserDescription"
from
	"CompanyPlan"
	join "CompanyCarrier" on ("CompanyCarrier"."Id" = "CompanyPlan"."CarrierId" )
	join "CompanyPlanType" on ( "CompanyPlanType"."CarrierId" = "CompanyCarrier"."Id" and "CompanyPlanType"."Id" = "CompanyPlan"."PlanTypeId" )
where
	"CompanyPlan"."CompanyId" = ?
	and "CompanyPlan"."UserDescription" is null
