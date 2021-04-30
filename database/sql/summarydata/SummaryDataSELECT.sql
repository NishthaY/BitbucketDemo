select
	"SummaryData".*
	, "CompanyCarrier"."UserDescription" as "CarrierDescription"
	, "CompanyPlanType"."UserDescription" as "PlanTypeDescription"
	, "CompanyPlan"."UserDescription" as "PlanDescription"
	, "CompanyCoverageTier"."UserDescription" as "CoverageTierDescription"
from
	"SummaryData"
	join "CompanyCarrier" on ( "CompanyCarrier"."Id" = "SummaryData"."CarrierId" )
	join "CompanyPlanType" on ("CompanyPlanType"."Id" = "SummaryData"."PlanTypeId" )
	join "CompanyPlan" on ("CompanyPlan"."Id" = "SummaryData"."PlanId" )
	join "CompanyCoverageTier" on ("CompanyCoverageTier"."Id" = "SummaryData"."CoverageTierId" )
where
	"SummaryData"."CompanyId" = ?
	and "SummaryData"."ImportDate" = ?
order by "CompanyCarrier"."UserDescription" asc, "CompanyPlanType"."UserDescription" asc, "CompanyPlan"."UserDescription", "CompanyCoverageTier"."UserDescription"
