select
	"CompanyPlanType"."Id"
	, "CompanyPlanType"."CompanyId"
	, "CompanyCarrier"."CarrierNormalized"
	, "CompanyPlanType"."PlanTypeNormalized"
from
	"CompanyPlanType"
	join "CompanyCarrier" on ( "CompanyCarrier"."Id" = "CompanyPlanType"."CarrierId" )
where
	"CompanyPlanType"."CompanyId" = ?
	and "CompanyPlanType"."UserDescription" is null
