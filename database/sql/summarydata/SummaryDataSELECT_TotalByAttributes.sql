select
	count(*) as "Lives"
	, sum( coalesce("RelationshipData"."Volume", "ImportData"."Volume") ) as "Volume"
	, sum( coalesce("RelationshipData"."MonthlyCost", "ImportData"."MonthlyCost") ) as "Premium"
from
	"ImportData"
	left join "RelationshipData" on ( "RelationshipData"."ImportDataId" = "ImportData"."Id" )
	join "Age" on ( "Age"."ImportDataId" = "ImportData"."Id" )
	join "WashedData" on ( "WashedData"."ImportDataId" = "ImportData"."Id" )
	join "CompanyCarrier" on
	(
		"CompanyCarrier"."CompanyId" = "ImportData"."CompanyId"
		and "CompanyCarrier"."CarrierNormalized" = upper("ImportData"."Carrier")
	)
	join "CompanyPlanType" on
	(
		"CompanyPlanType"."CarrierId" =  "CompanyCarrier"."Id"
		and "CompanyPlanType"."PlanTypeNormalized" = upper("ImportData"."PlanType")
	)
	join "CompanyPlan" on
	(
		"CompanyPlan"."CarrierId" =  "CompanyCarrier"."Id"
		and "CompanyPlan"."PlanTypeId" = "CompanyPlanType"."Id"
		and "CompanyPlan"."PlanNormalized" = upper("ImportData"."Plan")
	)
	join "CompanyCoverageTier" on
	(
		"CompanyCoverageTier"."CarrierId" =  "CompanyCarrier"."Id"
		and "CompanyCoverageTier"."PlanTypeId" = "CompanyPlanType"."Id"
		and "CompanyCoverageTier"."PlanId" = "CompanyPlan"."Id"
		and "CompanyCoverageTier"."CoverageTierNormalized" = upper("ImportData"."CoverageTier")
	)
	left join "AgeBand" on (
		"AgeBand"."CompanyCoverageTierId" = "CompanyCoverageTier"."Id"
		and "Age"."Age" >= "AgeBandStart"
		and "Age"."Age" <= "AgeBandEnd"
	)
	join "SummaryData" on
	(
		"SummaryData"."CarrierId" = "CompanyCarrier"."Id"
		and "SummaryData"."PlanTypeId" = "CompanyPlanType"."Id"
		and "SummaryData"."PlanId" = "CompanyPlan"."Id"
		and "SummaryData"."CoverageTierId" = "CompanyCoverageTier"."Id"
		and "SummaryData"."AgeBandId" {AGEBAND}
		and "SummaryData"."TobaccoUser" {TOBACCOUSER}
		and "SummaryData"."ImportDate" = "ImportData"."ImportDate"
	)
where
	"ImportData"."CompanyId" = ?
	and "ImportData"."ImportDate" = ?
	and "ImportData"."Finalized" = false
	and "WashedData"."WashedOutFlg" = false
	and "CompanyPlanType"."Ignored" = false
	and "CompanyCarrier"."Id" = ?
	and "CompanyPlanType"."Id" = ?
	and "CompanyPlan"."Id" = ?
	and "CompanyCoverageTier"."Id" = ?
	and "AgeBand"."Id" {AGEBAND}
	and "ImportData"."TobaccoUser" {TOBACCOUSER}
group by "SummaryData"."CompanyId",  "SummaryData"."ImportDate",  "SummaryData"."CarrierId",  "SummaryData"."PlanTypeId",  "SummaryData"."PlanId",  "SummaryData"."CoverageTierId",  "SummaryData"."AgeBandId",  "SummaryData"."TobaccoUser"
