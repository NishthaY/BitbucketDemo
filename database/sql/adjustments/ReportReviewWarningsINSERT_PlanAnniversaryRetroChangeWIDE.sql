-- Take all automatic adjustments that are RETRO CHANGE and are WIDE (Inter-tier)
-- and create report review warnings indicating that automatic adjustments might
-- be required.
insert into "ReportReviewWarnings" ( "CompanyId", "ImportDataId", "ImportDate", "Issue" )
select
	"AutomaticAdjustment"."CompanyId"
	, "RetroData"."ImportDataId" as "ImportDataId"
	, "AutomaticAdjustment"."ImportDate"
	, CASE
		WHEN "AutomaticAdjustment"."MonthlyCost" < 0 THEN format('Retro Change in %s created charges prior to anniversary month of %s. Charge for %s of (%s) based on supplied data and may require manual adjustment if a different cost was in effect prior to anniversary.', "CompanyCarrier"."UserDescription", trim(to_char( to_date( "CompanyPlanType"."PlanAnniversaryMonth"::text, 'MM'), 'Month' )), trim(to_char("AutomaticAdjustment"."TargetDate", 'Month')), ltrim(to_char(abs("AutomaticAdjustment"."MonthlyCost"),'99,999,999,999,999,990D99')) )
		ELSE format('Retro Change in %s created charges prior to anniversary month of %s. Charge for %s of %s based on supplied data and may require manual adjustment if a different cost was in effect prior to anniversary.', "CompanyCarrier"."UserDescription", trim(to_char( to_date( "CompanyPlanType"."PlanAnniversaryMonth"::text, 'MM'), 'Month' )), trim(to_char("AutomaticAdjustment"."TargetDate", 'Month')), ltrim(to_char(abs("AutomaticAdjustment"."MonthlyCost"),'99,999,999,999,999,990D99')) )
	END as "Issue"
from
	"AutomaticAdjustment"
	join "AdjustmentType" on ( "AutomaticAdjustment"."AdjustmentType" = "AdjustmentType"."Id")
	join "CompanyPlanType" on ( "CompanyPlanType"."Id" = "AutomaticAdjustment"."PlanTypeId" )
	join "RetroData" on ("RetroData"."Id" = "AutomaticAdjustment"."RetroDataId" )
	join "CompanyCarrier" on ( "CompanyCarrier"."Id" = "CompanyPlanType"."CarrierId" )
where
	1=1
	and "CompanyPlanType"."PlanAnniversaryMonth" is not null
    and "AutomaticAdjustment"."ParentRetroDataId" is not null
	and "AdjustmentType"."Id" in ( 4, 5, 6 )
	and "AutomaticAdjustment"."CompanyId" = ?
	and "AutomaticAdjustment"."ImportDate" = ?
	and "AutomaticAdjustment"."TargetDate" < ?
	and "AutomaticAdjustment"."CarrierId" = ?
	and "AutomaticAdjustment"."PlanTypeId" = ?
	and "AutomaticAdjustment"."PlanId" = ?
	and "AutomaticAdjustment"."CoverageTierId" = ?
