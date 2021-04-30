select
	"CompanyPlan".*
	, aso."Id"::int as "ASOFeeCarrierId"
	, stop."Id"::int as "StopLossFeeCarrierId"
from
	"CompanyPlan"
	join "CompanyCarrier" on ( "CompanyCarrier"."Id" = "CompanyPlan"."CarrierId" )
	join "CompanyPlanType" on ( "CompanyPlanType"."Id" = "CompanyPlan"."PlanTypeId" )
	left join "CompanyCarrier" aso on ( aso."Id" = "CompanyPlan"."ASOFeeCarrierId" and aso."CompanyId" = ?)
	left join "CompanyCarrier" stop on ( stop."Id" = "CompanyPlan"."StopLossFeeCarrierId" and stop."CompanyId" = ?)
where
	"CompanyPlan"."CompanyId" = ?
	and "CompanyCarrier"."CarrierNormalized" = upper(?)
	and "CompanyPlanType"."PlanTypeNormalized" = upper(?)
	and "CompanyPlan"."PlanNormalized" = upper(?)
