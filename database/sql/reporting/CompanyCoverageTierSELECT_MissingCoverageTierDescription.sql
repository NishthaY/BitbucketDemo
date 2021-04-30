select
	"CompanyCoverageTier"."Id"
	, "CompanyCarrier"."CarrierNormalized"
	, "CompanyPlanType"."PlanTypeNormalized"
	, "CompanyPlan"."PlanNormalized"
	, "CompanyCoverageTier"."CoverageTierNormalized"
	, "CompanyCoverageTier"."UserDescription"
from
	"CompanyCoverageTier"
	join "CompanyCarrier"	on (  "CompanyCoverageTier"."CarrierId" = "CompanyCarrier"."Id" )
	join "CompanyPlanType" 	on (  "CompanyPlanType"."CarrierId" ="CompanyCarrier"."Id"	and "CompanyCoverageTier"."PlanTypeId" = "CompanyPlanType"."Id" )
	join "CompanyPlan" 	on (  "CompanyPlan"."CarrierId" = "CompanyCarrier"."Id" and "CompanyPlan"."PlanTypeId" = "CompanyPlanType"."Id" and "CompanyPlan"."Id" = "CompanyCoverageTier"."PlanId" )
where
	"CompanyCoverageTier"."CompanyId" = ?
	and "CompanyCoverageTier"."UserDescription" is null
