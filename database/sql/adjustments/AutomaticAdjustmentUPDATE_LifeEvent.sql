update "AutomaticAdjustment"
set "AdjustmentType" = 8
from (
	select
		"RetroDataLifeEvent"."LifeEvent"
		, "RetroDataLifeEvent"."CoverageStartDate"
		, "AutomaticAdjustment"."Id" as "AutomaticAdjustmentId"
		--, "AutomaticAdjustment".*
	from
		"AutomaticAdjustment"
		join "RetroDataLifeEvent" on (
			"RetroDataLifeEvent"."RetroDataId" = "AutomaticAdjustment"."RetroDataId"
		)
	where
		"AutomaticAdjustment"."CompanyId" = ?
		and "AutomaticAdjustment"."ImportDate" = ?
		and "RetroDataLifeEvent"."LifeEvent" = true
		and "AutomaticAdjustment"."TargetDate" <= "RetroDataLifeEvent"."CoverageStartDate"
		and ("Before-CoverageTierIdList"  is null or "Before-CoverageTierIdList" not like '%,%') -- Exclude Many2Many items.
) as subquery
where "AutomaticAdjustment"."Id" = subquery."AutomaticAdjustmentId"
