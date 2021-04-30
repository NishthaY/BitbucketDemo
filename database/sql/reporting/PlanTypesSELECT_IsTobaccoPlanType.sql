select
	"PlanTypes"."Tobacco" as "SupportsTobaccoAttribute"
from
	"CompanyCoverageTier"
	join "CompanyPlanType" on ( "CompanyPlanType"."Id" = "CompanyCoverageTier"."PlanTypeId" )
	join "PlanTypes" on ( "PlanTypes"."Name" = "CompanyPlanType"."PlanTypeCode" )
where
	"CompanyCoverageTier"."CompanyId" = ?
	and "CompanyCoverageTier"."Id" = ?
