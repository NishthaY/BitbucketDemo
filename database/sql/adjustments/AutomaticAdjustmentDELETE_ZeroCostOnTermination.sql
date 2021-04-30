-- $0 Cost on Termination:
-- When the system receives a record with an end date in the current month causing a wash so
-- the current month is not charged and it sees an intra-tier change to $0, the update cost
-- of $0 should not retro back to prior months.
update "AutomaticAdjustment" set "AdjustmentType" = 10 where "Id" in
(
	select
		"AutomaticAdjustment"."Id"
		--"RetroData"."ImportDate"
		--, "RetroData"."MonthlyCost"
		--, "RetroData"."Before-MonthlyCost"
		--, "RetroData"."AdjustmentType"
		--, "AutomaticAdjustment".*
	from
		"AutomaticAdjustment"
		join "RetroData" on (
			"RetroData"."Id" = "AutomaticAdjustment" ."RetroDataId"
			and "RetroData"."AdjustmentType" in ( 4, 5 ) -- Retro change, but not if the tier changed.
			and "RetroData"."CoverageEndDate" = "AutomaticAdjustment"."ImportDate"	-- New end date is import date.
			and "RetroData"."MonthlyCost" = 0 	-- No monthly cost.
		)
	where
		"AutomaticAdjustment"."CompanyId" = ?
		and "AutomaticAdjustment"."ImportDate" = ?
)
