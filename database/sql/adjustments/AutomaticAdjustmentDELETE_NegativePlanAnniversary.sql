delete from "AutomaticAdjustment" where "Id" in
(
    select
        "AutomaticAdjustment"."Id" as "AutomaticAdjustmentId"
    from
        "AutomaticAdjustment"
        join "AdjustmentType" on ( "AutomaticAdjustment"."AdjustmentType" = "AdjustmentType"."Id")
        join "CompanyPlanType" on ( "CompanyPlanType"."Id" = "AutomaticAdjustment"."PlanTypeId" )
    where
        1=1
        and "CompanyPlanType"."PlanAnniversaryMonth" is not null
        and "AdjustmentType"."Id" = 2                                 -- Retro Adds Only.
        and "AutomaticAdjustment"."CompanyId" = ?
        and "AutomaticAdjustment"."ImportDate" = ?
        and "AutomaticAdjustment"."TargetDate" < ?
        and "AutomaticAdjustment"."CarrierId" = ?
        and "AutomaticAdjustment"."PlanTypeId" = ?
        and "AutomaticAdjustment"."PlanId" = ?
        and "AutomaticAdjustment"."CoverageTierId" = ?
        and "AutomaticAdjustment"."MonthlyCost" < 0                   -- Remove negative adjustments.
)