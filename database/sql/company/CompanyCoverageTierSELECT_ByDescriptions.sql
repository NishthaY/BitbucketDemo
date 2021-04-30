select
	"CompanyCoverageTier".*
from
	"CompanyCoverageTier"
	join "CompanyCarrier" on ( "CompanyCarrier"."Id" = "CompanyCoverageTier"."CarrierId" )
	join "CompanyPlanType" on ( "CompanyPlanType"."Id" = "CompanyCoverageTier"."PlanTypeId" )
	join "CompanyPlan" on ( "CompanyPlan"."Id" = "CompanyCoverageTier"."PlanId" )
where
	"CompanyCoverageTier"."CompanyId" = ?
	and "CompanyCarrier"."CarrierNormalized" = upper(?)
	and "CompanyPlanType"."PlanTypeNormalized" = upper(?)
	and "CompanyPlan"."PlanNormalized" = upper(?)
	and "CompanyCoverageTier"."CoverageTierNormalized" = upper(?)
