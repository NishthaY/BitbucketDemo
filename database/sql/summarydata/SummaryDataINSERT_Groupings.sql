insert into "SummaryData" ( "PreparedDate", "CompanyId", "ImportDate", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId", "AgeBandId", "TobaccoUser" )
select "PreparedDate", "CompanyId", "ImportDate", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId", "AgeBandId", "TobaccoUser" from
(
	-- Find all unique lives via the import data.
	select
		to_date(to_char( now() , 'MM/DD/YYYY'), 'MM/DD/YYYY') as "PreparedDate"
		, "ImportData"."CompanyId" as "CompanyId"
		, "ImportData"."ImportDate" as "ImportDate"
		, "CompanyCarrier"."Id" as "CarrierId"
		, "CompanyPlanType"."Id" as "PlanTypeId"
		, "CompanyPlan"."Id" as "PlanId"
		, "CompanyCoverageTier"."Id" as "CoverageTierId"
		, CASE WHEN "CompanyCoverageTier"."AgeBandIgnored" = true THEN null ELSE "AgeBand"."Id" END as "AgeBandId"
		, CASE
			WHEN "PlanTypes"."Name" is null THEN null					-- No PlanType, do not support tobacco.
			WHEN "PlanTypes"."Tobacco" = false THEN null				-- Plan Types that do not support tobacco return null.
			WHEN "CompanyCoverageTier"."TobaccoIgnored" = true THEN null 		-- Coverage tier says ignore plan type.
			ELSE "ImportData"."TobaccoUser" 					-- Use the value imported.
		END as "TobaccoUser"
	from
		"ImportData"
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
		left join "PlanTypes" on ( "CompanyPlanType"."PlanTypeCode" = "PlanTypes"."Name" )
		left join "AgeBand" on ( "CompanyCoverageTier"."Id" = "AgeBand"."CompanyCoverageTierId" )
	where
		"ImportData"."CompanyId" = ?
		and "ImportData"."ImportDate" = ?
		and "ImportData"."Finalized" = false
		and "WashedData"."WashedOutFlg" = false
		and "CompanyPlanType"."Ignored" = false
		and "CompanyCarrier"."Id" = ?

	UNION ALL

	-- Find all unique lives via the Automatic Adjustments
	select
		to_date(to_char( now() , 'MM/DD/YYYY'), 'MM/DD/YYYY') as "PreparedDate"
		, "AutomaticAdjustment"."CompanyId" as "CompanyId"
		, "AutomaticAdjustment"."ImportDate" as "ImportDate"
		, "AutomaticAdjustment"."CarrierId" as "CarrierId"
		, "AutomaticAdjustment"."PlanTypeId" as "PlanTypeId"
		, "AutomaticAdjustment"."PlanId" as "PlanId"
		, "AutomaticAdjustment"."CoverageTierId" as "CoverageTierId"
		, CASE WHEN "CompanyCoverageTier"."AgeBandIgnored" = true THEN null ELSE "AgeBand"."Id" END as "AgeBandId"
		, CASE
			WHEN "PlanTypes"."Name" is null THEN null					-- No PlanType, do not support tobacco.
			WHEN "PlanTypes"."Tobacco" = false THEN null				-- Plan Types that do not support tobacco return null.
			WHEN "CompanyCoverageTier"."TobaccoIgnored" = true THEN null 		-- Coverage tier says ignore plan type.
			ELSE "ImportData"."TobaccoUser" 					-- Use the value imported.
		END as "TobaccoUser"

	from
		"AutomaticAdjustment"
		join "CompanyCoverageTier" on
		(
			"CompanyCoverageTier"."CarrierId" =  "AutomaticAdjustment"."CarrierId"
			and "CompanyCoverageTier"."PlanTypeId" = "AutomaticAdjustment"."PlanTypeId"
			and "CompanyCoverageTier"."PlanId" = "AutomaticAdjustment"."PlanId"
			and "CompanyCoverageTier"."Id" = "AutomaticAdjustment"."CoverageTierId"
		)
		join "CompanyPlanType" on
		(
			"CompanyPlanType"."CarrierId" =  "AutomaticAdjustment"."CarrierId"
			and "CompanyPlanType"."Id" = "AutomaticAdjustment"."PlanTypeId"

		)
		left join "PlanTypes" on ( "CompanyPlanType"."PlanTypeCode" = "PlanTypes"."Name" )
		left join "AgeBand" on ( "CompanyCoverageTier"."Id" = "AgeBand"."CompanyCoverageTierId" )
		join "RetroData" on ( "RetroData"."Id" = "AutomaticAdjustment"."RetroDataId" )
		join "ImportData" on ( "ImportData"."Id" = "RetroData"."ImportDataId" )

	where
		"AutomaticAdjustment"."CompanyId" = ?
		and "AutomaticAdjustment"."ImportDate" = ?
		and "AutomaticAdjustment"."CarrierId" = ?
) as tbl
group by "PreparedDate", "CompanyId", "ImportDate", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId", "AgeBandId", "TobaccoUser"
