select "CarrierId", "PlanTypeId" from
(
	select
		"CompanyPlan"."CarrierId" as "CarrierId"
		, "CompanyPlan"."PlanTypeId" as "PlanTypeId"
	from
		"SummaryData" sd
		join "CompanyPlan" on ( "CompanyPlan"."Id" = sd."PlanId" )
	where
		sd."CompanyId" = ?
		and sd."ImportDate" = ?
		and "CompanyPlan"."ASOFee" is not null
		and "CompanyPlan"."PremiumEquivalent" = 't'
	group by "CompanyPlan"."ASOFeeCarrierId", "CompanyPlan"."ASOFeePlanTypeId","CompanyPlan"."CarrierId" , "CompanyPlan"."PlanTypeId"
	UNION ALL
	select
		"CompanyPlan"."CarrierId" as "CarrierId"
		, "CompanyPlan"."PlanTypeId" as "PlanTypeId"
	from
		"SummaryData" sd
		join "CompanyPlan" on ( "CompanyPlan"."Id" = sd."PlanId" )
	where
		sd."CompanyId" = ?
		and sd."ImportDate" = ?
		and "CompanyPlan"."StopLossFee" is not null
		and "CompanyPlan"."PremiumEquivalent" = 't'
	group by "CompanyPlan"."StopLossFeeCarrierId", "CompanyPlan"."StopLossFeePlanTypeId","CompanyPlan"."CarrierId" , "CompanyPlan"."PlanTypeId"
) as t
group by "CarrierId", "PlanTypeId"
