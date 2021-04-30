select
	"CompanyPlanType"."UserDescription" as benifit
	, sum("SummaryData"."Premium") as monthly_cost
	, sum("SummaryDataYTD"."PremiumYTD") as monthly_cost_ytd
	, sum("SummaryData"."AdjustedPremium") as wash_retro
	, sum("SummaryDataYTD"."AdjustedPremiumYTD") as wash_retro_ytd
from
	"SummaryData"
	join "CompanyPlanType" on ( "SummaryData"."PlanTypeId" = "CompanyPlanType"."Id" )
	left join "SummaryDataYTD" on (
		"SummaryData"."CompanyId" = "SummaryDataYTD"."CompanyId"
		and "SummaryData"."ImportDate" = "SummaryDataYTD"."ImportDate"
		and "SummaryData"."CarrierId" = "SummaryDataYTD"."CarrierId"
		and "SummaryData"."PlanTypeId" = "SummaryDataYTD"."PlanTypeId"
		and "SummaryData"."PlanId" = "SummaryDataYTD"."PlanId"
		and "SummaryData"."CoverageTierId" = "SummaryDataYTD"."CoverageTierId"
		and ( ("SummaryData"."AgeBandId" is null and "SummaryDataYTD"."AgeBandId" is null ) OR ("SummaryData"."AgeBandId" = "SummaryDataYTD"."AgeBandId" ) )
		and ( ("SummaryData"."TobaccoUser" is null and "SummaryDataYTD"."TobaccoUser" is null ) OR ("SummaryData"."TobaccoUser" = "SummaryDataYTD"."TobaccoUser" ) )
	)
where
	"SummaryData"."CompanyId" = ?
	and "SummaryData"."ImportDate" = ? -- Current Import
group by "CompanyPlanType"."UserDescription"
