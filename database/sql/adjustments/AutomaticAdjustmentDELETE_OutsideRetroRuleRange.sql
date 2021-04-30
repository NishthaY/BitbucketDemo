delete from "AutomaticAdjustment" where "Id" in
(
	-- Select any automatic adjustments that were created that are outside of the retro rule
	-- range tied to the CompanyPlanType
	select
		"AutomaticAdjustment"."Id" as "AutomaticAdjustmentId"
	from
		"AutomaticAdjustment"
		join "CompanyPlanType" on ("CompanyPlanType"."Id" = "AutomaticAdjustment"."PlanTypeId")
	where
		"AutomaticAdjustment"."CompanyId" = ?
		and "AutomaticAdjustment"."ImportDate" = ?
		and "AutomaticAdjustment"."TargetDate" < "AutomaticAdjustment"."ImportDate" +  ( ("CompanyPlanType"."RetroRule"::integer * -1) || ' month')::interval
)
