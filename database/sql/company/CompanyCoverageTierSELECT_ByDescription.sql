select
	"CompanyCoverageTier".*
from
	"CompanyCoverageTier"
	join "CompanyCarrier" on ( "CompanyCoverageTier"."CarrierId" = "CompanyCarrier"."Id" )
	join "CompanyPlanType" on ( "CompanyCoverageTier"."PlanTypeId" = "CompanyPlanType"."Id" )
	join "CompanyPlan" on ( "CompanyCoverageTier"."PlanId" = "CompanyPlan"."Id" )
where
	"CompanyCoverageTier"."CompanyId" = ?
	and "CompanyCoverageTier"."CarrierId" = ?
	and "CompanyCoverageTier"."PlanTypeId" = ?
	and "CompanyCoverageTier"."PlanId" = ?
	and "CompanyCoverageTier"."CoverageTierNormalized" = upper(?)
