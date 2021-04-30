select
	"CompanyPlan".*
from
	"CompanyPlan"
	join "CompanyCarrier" on ( "CompanyPlan"."CarrierId" = "CompanyCarrier"."Id" )
	join "CompanyPlanType" on ( "CompanyPlan"."PlanTypeId" = "CompanyPlanType"."Id" )
where
	"CompanyPlan"."CompanyId" = ?
	and "CompanyPlan"."CarrierId" = ?
	and "CompanyPlan"."PlanTypeId" = ?
	and "CompanyPlan"."PlanNormalized" = upper(?)
