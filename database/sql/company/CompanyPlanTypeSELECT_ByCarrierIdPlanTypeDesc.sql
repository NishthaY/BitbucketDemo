select
	"CompanyPlanType".*
from
	"CompanyPlanType"
	join "CompanyCarrier" on ( "CompanyPlanType"."CarrierId" = "CompanyCarrier"."Id" )
where
	"CompanyPlanType"."CompanyId" = ?
	and "CompanyCarrier"."Id" = ?
	and "CompanyPlanType"."PlanTypeNormalized" = upper(?)
