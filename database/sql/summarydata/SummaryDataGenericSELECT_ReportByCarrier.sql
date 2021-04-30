select
	"CompanyPlanType"."UserDescription" as "PlanTypeDescription"
	, "CompanyPlan"."UserDescription" as "PlanDescription"
	, "CompanyCoverageTier"."UserDescription" as "CoverageTierDescription"
	, data."Lives"
	, data."Volume"
	, data."Premium"
	, data."AdjustedLives"
	, data."AdjustedVolume"
	, data."AdjustedPremium"
	, data."Lives" + data."AdjustedLives" as "TotalLives"
	, data."Volume" + data."AdjustedVolume" as "TotalVolume"
	, data."Premium" + data."AdjustedPremium" as "TotalPremium"
	, "AgeBand"."AgeBandStart"
	, "AgeBand"."AgeBandEnd"
	, data."TobaccoUser"
from
	"{TABLENAME}" data
	join "CompanyCarrier" on ( "CompanyCarrier"."Id" = data."CarrierId" )
	left join "CompanyPlanType" on ("CompanyPlanType"."Id" = data."PlanTypeId" )
	left join "CompanyPlan" on ("CompanyPlan"."Id" = data."PlanId" )
	left join "CompanyCoverageTier" on ("CompanyCoverageTier"."Id" = data."CoverageTierId" )
	left join "AgeBand" on ( "AgeBand"."Id" = data."AgeBandId" )
where
	data."CompanyId" = ?
	and data."ImportDate" = ?
	and data."CarrierId" = ?
order by "CompanyCarrier"."UserDescription" asc, "CompanyPlanType"."UserDescription" asc, "CompanyPlan"."UserDescription" asc, "CompanyCoverageTier"."UserDescription" asc, "AgeBand"."AgeBandStart", data."TobaccoUser" asc
