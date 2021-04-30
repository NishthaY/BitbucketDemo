insert into "SummaryDataYTD" ( "CompanyId", "ImportDate", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId", "AgeBandId", "TobaccoUser", "PremiumYTD", "AdjustedPremiumYTD" )
select
	"SummaryData"."CompanyId"
	, ? as "ImportDate"
	, "SummaryData"."CarrierId"
	, "SummaryData"."PlanTypeId"
	, "SummaryData"."PlanId"
	, "SummaryData"."CoverageTierId"
	, "SummaryData"."AgeBandId"
	, "SummaryData"."TobaccoUser"
	, sum( "SummaryData"."Premium" ) as "PremiumYTD"
	, sum( "SummaryData"."AdjustedPremium" ) as "AdjustedPremiumYTD"
from
	"SummaryData"
	join "CompanyPlanType" on ( "SummaryData"."PlanTypeId" = "CompanyPlanType"."Id" )
where
	"SummaryData"."CompanyId" = ?
	and "SummaryData"."ImportDate" <= ? -- Current Import
	and "SummaryData"."ImportDate" >= to_date(format('01/01/%s', to_char(DATE ?, 'YYYY')), 'MM/DD/YYYY') -- First Import This Year.
group by "SummaryData"."CompanyId", "SummaryData"."CarrierId", "SummaryData"."PlanTypeId", "SummaryData"."PlanId", "SummaryData"."CoverageTierId", "SummaryData"."AgeBandId", "SummaryData"."TobaccoUser"
