insert into "CompanyCoverageTier" ( "CompanyId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierNormalized", "UserDescription", "AgeBandIgnored", "TobaccoIgnored")
	select
		"CompanyPlan"."CompanyId"
		, "CompanyPlan"."StopLossFeeCarrierId" as "CarrierId"
		, "CompanyPlan"."StopLossFeePlanTypeId" as "PlanTypeId"
		, plan."Id" as "PlanId"
		, upper("CompanyCoverageTier"."CoverageTierNormalized")
		, "CompanyCoverageTier"."UserDescription"
		, true as "AgeBandIgnored"
		, true as "TobaccoIgnored"
	from
		"CompanyPlan"
		join "CompanyCarrier" on ( "CompanyCarrier"."CompanyId" = "CompanyPlan"."CompanyId" and "CompanyCarrier"."Id" = "CompanyPlan"."StopLossFeeCarrierId" )
		join "CompanyPlanType" on ( "CompanyPlanType"."CompanyId" = "CompanyPlan"."CompanyId" and "CompanyPlanType"."Id" = "CompanyPlan"."StopLossFeePlanTypeId" )
		join "CompanyCoverageTier" on (
			"CompanyCoverageTier"."CarrierId" = "CompanyPlan"."CarrierId"
			and "CompanyCoverageTier"."PlanTypeId" = "CompanyPlan"."PlanTypeId"
			and "CompanyCoverageTier"."PlanId" = "CompanyPlan"."Id"
		)
		left join "CompanyCoverageTier" tier on (
			tier."CarrierId" = "CompanyPlan"."StopLossFeeCarrierId"
			and tier."PlanTypeId" = "CompanyPlan"."StopLossFeePlanTypeId"
			and tier."PlanId" = "CompanyPlan"."Id"
		)
		left join "CompanyPlan" plan on (
			plan."CarrierId" = "CompanyPlan"."StopLossFeeCarrierId"
			and plan."PlanTypeId" = "CompanyPlan"."StopLossFeePlanTypeId"
			and plan."PlanNormalized" = "CompanyPlan"."PlanNormalized"
		)
		left join "CompanyCoverageTier" tCheck on (
			tCheck."CompanyId" = "CompanyPlan"."CompanyId"
			and tCheck."CarrierId" = "CompanyPlan"."StopLossFeeCarrierId"
			and tCheck."PlanTypeId" = "CompanyPlan"."StopLossFeePlanTypeId"
			and tCheck."PlanId" = plan."Id"
			and tCheck."CoverageTierNormalized" = upper("CompanyCoverageTier"."CoverageTierNormalized")
		)
	where
		"CompanyPlan"."CompanyId" = ?
		and "CompanyPlan"."StopLossFee" is not null
		and "CompanyPlan"."StopLossFeeCarrierId" is not null
		and "CompanyPlan"."StopLossFeePlanTypeId" is not null
		and tier."UserDescription" is null
		and tCheck."Id" is null
