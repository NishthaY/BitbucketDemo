update "AutomaticAdjustment" set "AdjustmentType" = 7 where "Id" in (

	select
		"AutomaticAdjustment"."Id"
	from
		"AutomaticAdjustment"
		join "AdjustmentType" on ( "AutomaticAdjustment"."AdjustmentType" = "AdjustmentType"."Id")
		join "CompanyPlanType" on ( "CompanyPlanType"."Id" = "AutomaticAdjustment"."PlanTypeId" )
		join "RetroData" on ("RetroData"."Id" = "AutomaticAdjustment"."RetroDataId" )
	where
		1=1
		and "CompanyPlanType"."PlanAnniversaryMonth" is not null
		and "AutomaticAdjustment"."ParentRetroDataId" is null		-- NARROW
		and "AdjustmentType"."Id" in ( 4, 5, 6 )			       -- RETRO CHANGE
		and "AutomaticAdjustment"."CompanyId" = ?
		and "AutomaticAdjustment"."ImportDate" = ?
		and "AutomaticAdjustment"."TargetDate" < ?
		and "AutomaticAdjustment"."CarrierId" = ?
		and "AutomaticAdjustment"."PlanTypeId" = ?
		and "AutomaticAdjustment"."PlanId" = ?
		and "AutomaticAdjustment"."CoverageTierId" = ?

)
