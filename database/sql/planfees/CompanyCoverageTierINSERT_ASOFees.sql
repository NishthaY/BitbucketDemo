insert into "CompanyCoverageTier" ( "CompanyId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierNormalized", "UserDescription", "AgeBandIgnored", "TobaccoIgnored")
	select
		"CompanyPlan"."CompanyId"
		, "CompanyPlan"."ASOFeeCarrierId" as "CarrierId"
		, "CompanyPlan"."ASOFeePlanTypeId" as "PlanTypeId"
		, plan."Id" as "PlanId"
		, upper("CompanyCoverageTier"."CoverageTierNormalized")
		, "CompanyCoverageTier"."UserDescription"
		, true as "AgeBandIgnored"
		, true as "TobaccoIgnored"
	from
		"CompanyPlan"
		join "CompanyCarrier" on ( "CompanyCarrier"."CompanyId" = "CompanyPlan"."CompanyId" and "CompanyCarrier"."Id" = "CompanyPlan"."ASOFeeCarrierId" )
		join "CompanyPlanType" on ( "CompanyPlanType"."CompanyId" = "CompanyPlan"."CompanyId" and "CompanyPlanType"."Id" = "CompanyPlan"."ASOFeePlanTypeId" )
		join "CompanyCoverageTier" on (
			"CompanyCoverageTier"."CarrierId" = "CompanyPlan"."CarrierId"
			and "CompanyCoverageTier"."PlanTypeId" = "CompanyPlan"."PlanTypeId"
			and "CompanyCoverageTier"."PlanId" = "CompanyPlan"."Id"
		)
		left join "CompanyCoverageTier" tier on (
			tier."CarrierId" = "CompanyPlan"."ASOFeeCarrierId"
			and tier."PlanTypeId" = "CompanyPlan"."ASOFeePlanTypeId"
			and tier."PlanId" = "CompanyPlan"."Id"
		)
		left join "CompanyPlan" plan on (
			plan."CarrierId" = "CompanyPlan"."ASOFeeCarrierId"
			and plan."PlanTypeId" = "CompanyPlan"."ASOFeePlanTypeId"
			and plan."PlanNormalized" = "CompanyPlan"."PlanNormalized"
		)
		left join "CompanyCoverageTier" tCheck on (
			tCheck."CompanyId" = "CompanyPlan"."CompanyId"
			and tCheck."CarrierId" = "CompanyPlan"."ASOFeeCarrierId"
			and tCheck."PlanTypeId" = "CompanyPlan"."ASOFeePlanTypeId"
			and tCheck."PlanId" = plan."Id"
			and tCheck."CoverageTierNormalized" = upper("CompanyCoverageTier"."CoverageTierNormalized")
		)
	where
		"CompanyPlan"."CompanyId" = ?
		and "CompanyPlan"."ASOFee" is not null
		and "CompanyPlan"."ASOFeeCarrierId" is not null
		and "CompanyPlan"."ASOFeePlanTypeId" is not null
		and tier."UserDescription" is null
		and tCheck."Id" is null
