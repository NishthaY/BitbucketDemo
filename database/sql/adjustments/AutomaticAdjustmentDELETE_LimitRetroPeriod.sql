-- Limit Retro Period
-- When the coverage start date does not change but the monthly cost and/or
-- premium does change, the retros applied go to the LATER of either the
-- coverage start date OR the retro look-back period.

update "AutomaticAdjustment" set "AdjustmentType" = 9 where "Id" in (
	select
		"AutomaticAdjustment"."Id"
		--"AdjustmentType"."Name" as "AdjustmentCode"
		--, "AdjustmentType"."Id" as "AdjustmentTypeId"
		--, "RetroData"."Before-CoverageStartDate"
		--, "RetroData"."CoverageStartDate"
		--, "RetroData"."Volume"
		--, "RetroData"."Before-Volume"
		--, "RetroData"."MonthlyCost"
		--, "RetroData"."Before-MonthlyCost"
		--, "CompanyPlanType"."WashRule"
		--, "AutomaticAdjustment".*

	from
		"AutomaticAdjustment"
		join "RetroData" on ( "RetroData"."Id" = "AutomaticAdjustment"."RetroDataId" )
		join "AdjustmentType" on ( "AdjustmentType"."Id" = "AutomaticAdjustment"."AdjustmentType" )
		join "CompanyPlanType" on ("CompanyPlanType"."Id" = "AutomaticAdjustment"."PlanTypeId" )

	where
		-- This company
		"AutomaticAdjustment"."CompanyId" = ?

		-- This monthly import
		and "AutomaticAdjustment"."ImportDate" = ?

		-- Only the Retro Change items caused by a monthly cost or volume change.
		and "AutomaticAdjustment"."AdjustmentType" = 5

		-- No coverage start date change between this and last month.
		and "RetroData"."Before-CoverageStartDate" = "RetroData"."CoverageStartDate"

		-- Money or volume change between this and last month.
		and (
			"RetroData"."MonthlyCost" != "RetroData"."Before-MonthlyCost"::numeric(18,4)
			or "RetroData"."Volume" != "RetroData"."Before-Volume"::numeric(18,4)
		)

		-- Find prior months.
		and
		(
			(
				-- Wash Rule 1st
				"CompanyPlanType"."WashRule"::int = 1
				and "RetroData"."CoverageStartDate" >= "AutomaticAdjustment"."TargetDate" + interval '1 month'
			)
			OR
			(
				-- Wash Rule 15st
				"CompanyPlanType"."WashRule"::int = 15
				and "RetroData"."CoverageStartDate" >= "AutomaticAdjustment"."TargetDate" + interval '15 days'
			)
		)
)
