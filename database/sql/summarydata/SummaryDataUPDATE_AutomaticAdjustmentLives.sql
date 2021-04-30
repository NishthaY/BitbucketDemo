update "SummaryData" target set
	"AdjustedLives" = subquery."AdjustedLives"
from
(
	select
		"AutomaticAdjustment"."CompanyId"
		, "AutomaticAdjustment"."ImportDate"
		, "AutomaticAdjustment"."CarrierId"
		, "AutomaticAdjustment"."PlanTypeId"
		, "AutomaticAdjustment"."PlanId"
		, "AutomaticAdjustment"."CoverageTierId"
		, "AgeBand"."Id" as "AgeBandId"
		, CASE
			WHEN "PlanTypes"."Name" is null THEN null												-- No PlanType, do not support tobacco.
			WHEN "PlanTypes"."Tobacco" = false THEN null											-- Plan Types that do not support tobacco return null.
			WHEN "CompanyCoverageTier"."TobaccoIgnored" = true then null							-- Coverage tier says ignore plan type
			WHEN "CompanyCoverageTier"."TobaccoIgnored" = false then "ImportData"."TobaccoUser"		-- Use the value imported
		END as "TobaccoUser"
		, sum(
			CASE
				WHEN "AutomaticAdjustment"."AdjustmentType" = 2 THEN 1                                                      -- RETRO ADD
				WHEN "AutomaticAdjustment"."AdjustmentType" = 3 THEN -1                                                     -- RETRO TERM
				WHEN "AutomaticAdjustment"."AdjustmentType" = 4 AND "AutomaticAdjustment"."MonthlyCost" < 0 THEN -1         -- RETRO CHANGE loss
				WHEN "AutomaticAdjustment"."AdjustmentType" = 4 AND "AutomaticAdjustment"."MonthlyCost" >= 0 THEN 1         -- RETRO CHANGE gain
				ELSE 0
			END
		) as "AdjustedLives"
	from
		"AutomaticAdjustment"
		join "AdjustmentType" on ( "AdjustmentType"."Id" = "AutomaticAdjustment"."AdjustmentType" )
		join "RetroData" on ( "RetroData"."Id" = "AutomaticAdjustment"."RetroDataId" )
		join "ImportData" on ("ImportData"."Id" = "RetroData"."ImportDataId" )
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
		and "AdjustmentType"."Ignored" = false	-- Exclude Ignored Adjustments.
	group by
		"AutomaticAdjustment"."CompanyId"
		, "AutomaticAdjustment"."ImportDate"
		, "AutomaticAdjustment"."CarrierId"
		, "AutomaticAdjustment"."PlanTypeId"
		, "AutomaticAdjustment"."PlanId"
		, "AutomaticAdjustment"."CoverageTierId"
		, "AgeBand"."Id"
		, "CompanyCoverageTier"."TobaccoIgnored"
		, "PlanTypes"."Name"
		, "PlanTypes"."Tobacco"
		, CASE
			WHEN "PlanTypes"."Name" is null THEN null												-- No PlanType, do not support tobacco.
			WHEN "PlanTypes"."Tobacco" = false THEN null											-- Plan Types that do not support tobacco return null.
			WHEN "CompanyCoverageTier"."TobaccoIgnored" = true then null							-- Coverage tier says ignore plan type
			WHEN "CompanyCoverageTier"."TobaccoIgnored" = false then "ImportData"."TobaccoUser"		-- Use the value imported
		END
) as subquery
where
	target."CompanyId" = subquery."CompanyId"
	and target."ImportDate" = subquery."ImportDate"
	and target."CarrierId" = subquery."CarrierId"
	and target."PlanTypeId" = subquery."PlanTypeId"
	and target."PlanId" = subquery."PlanId"
	and target."CoverageTierId" = subquery."CoverageTierId"
	and (( target."AgeBandId" is null AND  subquery."AgeBandId" is null) OR target."AgeBandId" = subquery."AgeBandId" )
	and (( target."TobaccoUser" is null AND  subquery."TobaccoUser" is null) OR target."TobaccoUser" = subquery."TobaccoUser" )
