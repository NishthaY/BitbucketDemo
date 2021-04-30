update "AutomaticAdjustment"
set "AdjustmentType" = 8
from (
	select
		"RetroDataLifeEvent"."LifeEvent"
		, "RetroDataLifeEvent"."CoverageStartDate"
		, "AutomaticAdjustment"."Id" as "AutomaticAdjustmentId"
	from
		"AutomaticAdjustment"
		join "RetroDataLifeEvent" on (
			"RetroDataLifeEvent"."RetroDataId" = "AutomaticAdjustment"."RetroDataId"
		)
	where
		"AutomaticAdjustment"."CompanyId" = ?
		and "AutomaticAdjustment"."ImportDate" = ?
		and "RetroDataLifeEvent"."LifeEvent" = true
		and "AutomaticAdjustment"."TargetDate" <= ?
		and "Before-CoverageTierIdList" = ?
) as subquery
where "AutomaticAdjustment"."Id" = subquery."AutomaticAdjustmentId"
