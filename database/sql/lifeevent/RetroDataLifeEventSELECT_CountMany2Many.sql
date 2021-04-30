select
	count(*) as "Count"
from
	"AutomaticAdjustment"
	join "RetroDataLifeEvent" on (
		"RetroDataLifeEvent"."RetroDataId" = "AutomaticAdjustment"."RetroDataId"
	)
where
	"AutomaticAdjustment"."CompanyId" = ?
	and "AutomaticAdjustment"."ImportDate" = ?
	and "RetroDataLifeEvent"."LifeEvent" = true
    and "Before-CoverageTierIdList" = ?
	and "AutomaticAdjustment"."TargetDate" <= ?
