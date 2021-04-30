select
		"ManualAdjustment"."CompanyId"
		, "ManualAdjustment"."ImportDate"
		, "ManualAdjustment"."CarrierId"
		, "ManualAdjustment"."PlanTypeId"
		, "ManualAdjustment"."PlanId"
		, "ManualAdjustment"."CoverageTierId"
		, null as "AgeBandId"
		, null as "TobaccoUser"
		, 0.00 as "TotalAdjustedVolume"
		, sum("ManualAdjustment"."Amount") as "TotalAdjustedPremium"
	from
		"ManualAdjustment"
	where
		"ManualAdjustment"."CompanyId" = ?
		and "ManualAdjustment"."ImportDate" = ?
	group by "CompanyId", "ImportDate", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId"
