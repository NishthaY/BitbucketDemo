-- Collecting the adjustments requires two queries.

-- QUERY1: We select all of the NARROW data.  This means data that can directly map the adjustment back to a line
-- in the import data.

-- QUERY2: We select all of the WIDE data.  This means that any adjustments created due to a RetroChange
-- that cannot be directly mapped back to the import data.  These are identified by automatic adjustments
-- that have a parent retro id.

-- This query will take the results from Query1 and Query2 and merge them together totalling the volume and premium
select "CompanyId", "ImportDate", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId", "AgeBandId", "TobaccoUser", sum("TotalAdjustedVolume") as "TotalAdjustedVolume", sum("TotalAdjustedPremium") as "TotalAdjustedPremium" from
(

	-- Select all of the adjustments that can be directly mapped back to retrodata/importdata.
	-- Group them with their attributes.
	select
		"AutomaticAdjustment"."CompanyId"
		, "AutomaticAdjustment"."ImportDate"
		, "AutomaticAdjustment"."CarrierId"
		, "AutomaticAdjustment"."PlanTypeId"
		, "AutomaticAdjustment"."PlanId"
		, "AutomaticAdjustment"."CoverageTierId"
		, CASE WHEN "CompanyCoverageTier"."AgeBandIgnored" = true THEN null ELSE "AgeBand"."Id" END as "AgeBandId"
		, CASE
			WHEN "PlanTypes"."Name" is null THEN null						-- No PlanType, do not support tobacco.
			WHEN "PlanTypes"."Tobacco" = false THEN null					-- Plan Types that do not support tobacco return null.
			WHEN "CompanyCoverageTier"."TobaccoIgnored" = true THEN null 	-- Coverage tier says ignore plan type.
			ELSE "ImportData"."TobaccoUser" 								-- Use the value imported.
		END as "TobaccoUser"
		, "AutomaticAdjustment"."Volume" as "TotalAdjustedVolume"
		, "AutomaticAdjustment"."MonthlyCost" as "TotalAdjustedPremium"
	from
		"AutomaticAdjustment"
		join "AdjustmentType" on ( "AdjustmentType"."Id" = "AutomaticAdjustment"."AdjustmentType" )
		join "RetroData" on ( "RetroData"."Id" = "AutomaticAdjustment"."RetroDataId" )
		join "ImportData" on ("ImportData"."Id" = "RetroData"."ImportDataId" )
		join "CompanyPlanType" on ( "CompanyPlanType"."Id" = "AutomaticAdjustment"."PlanTypeId" )
		join "CompanyPlan" on ( "CompanyPlan"."Id" = "AutomaticAdjustment"."PlanId" )
		join "CompanyCoverageTier" on ( "CompanyCoverageTier"."Id" = "AutomaticAdjustment"."CoverageTierId" )
		join "Age" on ( "ImportData"."Id" = "Age"."ImportDataId" )
		left join "AgeBand" on
		(
			"AutomaticAdjustment"."CoverageTierId" = "AgeBand"."CompanyCoverageTierId"
			and "Age"."Age" >= "AgeBand"."AgeBandStart"
			and "Age"."Age" <= "AgeBand"."AgeBandEnd"
		)
		left join "PlanTypes" on ( "CompanyPlanType"."PlanTypeCode" = "PlanTypes"."Name" )
	where
		"AutomaticAdjustment"."CompanyId" = ?
		and "AutomaticAdjustment"."ImportDate" = ?
		and "AutomaticAdjustment"."ParentRetroDataId" is null -- NARROW ONLY
		and "AdjustmentType"."Ignored" = false	-- Exclude Ignored Adjustments.

	UNION ALL


	-- select all of the adjustments that were not in the original importdata file ( retro change ).  These are
	-- identified because they have a parent it in the adjustment data.  Pull those and group them under thier
	-- parent key.
	select
			"AutomaticAdjustment"."CompanyId"
			, "AutomaticAdjustment"."ImportDate"
			, "AutomaticAdjustment"."CarrierId"
			, "AutomaticAdjustment"."PlanTypeId"
			, "AutomaticAdjustment"."PlanId"
			, "AutomaticAdjustment"."CoverageTierId"
			, CASE WHEN "CompanyCoverageTier"."AgeBandIgnored" = true THEN null ELSE "AgeBand"."Id" END as "AgeBandId"
			, CASE
				WHEN "PlanTypes"."Name" is null THEN null						-- No PlanType, do not support tobacco.
				WHEN "PlanTypes"."Tobacco" = false THEN null					-- Plan Types that do not support tobacco return null.
				WHEN "CompanyCoverageTier"."TobaccoIgnored" = true THEN null 	-- Coverage tier says ignore plan type.
				ELSE "ImportData"."TobaccoUser" 								-- Use the value imported.
			END as "TobaccoUser"
			, "AutomaticAdjustment"."Volume" as "TotalAdjustedVolume"
			, "AutomaticAdjustment"."MonthlyCost" as "TotalAdjustedPremium"
		from
			"AutomaticAdjustment"
			join "AdjustmentType" on ( "AdjustmentType"."Id" = "AutomaticAdjustment"."AdjustmentType" )
			join "RetroData" on ("RetroData"."Id" = "AutomaticAdjustment"."RetroDataId" )
			join "ImportData" on ( "ImportData"."Id" = "RetroData"."ImportDataId" )
			join "CompanyPlan" on ( "CompanyPlan"."Id" = "AutomaticAdjustment"."PlanId" )
			join "CompanyPlanType" on ( "CompanyPlanType"."Id" = "AutomaticAdjustment"."PlanTypeId" )
			join "CompanyCoverageTier" on ( "CompanyCoverageTier"."Id" = "AutomaticAdjustment"."CoverageTierId" )
			join "Age" on ( "ImportData"."Id" = "Age"."ImportDataId" )
			left join "AgeBand" on
			(
				"AutomaticAdjustment"."CoverageTierId" = "AgeBand"."CompanyCoverageTierId"
				and "Age"."Age" >= "AgeBand"."AgeBandStart"
				and "Age"."Age" <= "AgeBand"."AgeBandEnd"
			)
			left join "PlanTypes" on ( "CompanyPlanType"."PlanTypeCode" = "PlanTypes"."Name" )
		where
			"AutomaticAdjustment"."CompanyId" = ?
			and "AutomaticAdjustment"."ImportDate" = ?
			and "AutomaticAdjustment"."ParentRetroDataId" is not null	-- WIDE ONLY
			and "AdjustmentType"."Ignored" = false	-- Exclude Ignored Adjustments.

) as tbl
group by "CompanyId", "ImportDate", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId", "AgeBandId", "TobaccoUser"
