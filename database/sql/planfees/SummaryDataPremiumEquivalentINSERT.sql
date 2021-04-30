-- COPY the SummaryData records to the SummaryDataPremiumEquivalent table.
insert into "SummaryDataPremiumEquivalent" ( "CompanyId", "PreparedDate", "ImportDate", "ParentCarrierId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId", "AgeBandId", "TobaccoUser", "Lives", "Volume", "Premium", "AdjustedLives", "AdjustedVolume", "AdjustedPremium" )
select
	sd."CompanyId"
	, sd."PreparedDate"
	, sd."ImportDate"
	, ? as "ParentCarrierId"
	, ? as "CarrierId"
	, sd."PlanTypeId"
	, sd."PlanId"
	, sd."CoverageTierId"
	, sd."AgeBandId"
	, sd."TobaccoUser"
	, sd."Lives"
	, sd."Volume"
	, sd."Premium"
	, sd."AdjustedLives"
	, sd."AdjustedVolume"
	, sd."AdjustedPremium"
from
	"SummaryData" sd
	join "CompanyPlan" on ( "CompanyPlan"."Id" = sd."PlanId" )
where
	sd."CompanyId" = ?
	and sd."ImportDate" = ?
	and sd."CarrierId" = ?
	and sd."PlanTypeId" = ?
	and "CompanyPlan"."PremiumEquivalent" = true
