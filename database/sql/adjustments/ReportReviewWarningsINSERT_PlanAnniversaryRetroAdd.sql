-- Take all automatic adjustments that are RETRO ADD that have crossed the
-- Plan Anniversary Month and write ReportReviewWarings.
--
insert into "ReportReviewWarnings" ( "CompanyId", "ImportDataId", "ImportDate", "Issue" )
	select
		"AutomaticAdjustment"."CompanyId"
		, "RetroData"."ImportDataId" as "ImportDataId"
		, "AutomaticAdjustment"."ImportDate"
		, CASE
			WHEN "AutomaticAdjustment"."MonthlyCost" < 0 THEN format('Retro Add in %s created charges prior to anniversary month of %s. Charge for %s of (%s) based on supplied data and may require manual adjustment if a different cost was in effect prior to anniversary.', "CompanyCarrier"."UserDescription", trim(to_char( to_date( "CompanyPlanType"."PlanAnniversaryMonth"::text, 'MM'), 'Month' )), trim(to_char("AutomaticAdjustment"."TargetDate", 'Month')), ltrim(to_char(abs("AutomaticAdjustment"."MonthlyCost"),'99,999,999,999,999,990D99')) )
			ELSE format('Retro Add in %s created charges prior to anniversary month of %s. Charge for %s of %s based on supplied data and may require manual adjustment if a different cost was in effect prior to anniversary.', "CompanyCarrier"."UserDescription", trim(to_char( to_date( "CompanyPlanType"."PlanAnniversaryMonth"::text, 'MM'), 'Month' )), trim(to_char("AutomaticAdjustment"."TargetDate", 'Month')), ltrim(to_char(abs("AutomaticAdjustment"."MonthlyCost"),'99,999,999,999,999,990D99')) )
		END as "Issue"
		--, to_char( to_date( "CompanyPlanType"."PlanAnniversaryMonth"::text, 'MM'), 'Month' ) as "##anniversarymonth##"
		--, "CompanyCarrier"."UserDescription" as "##carrier##"
		--, to_char("AutomaticAdjustment"."TargetDate", 'Month') as "##chargemonth##"
		--, ltrim(to_char(abs("AutomaticAdjustment"."MonthlyCost"),'99,999,999,999,999,990D99')) as "##chargeamount##"
	from
		"AutomaticAdjustment"
		join "AdjustmentType" on ( "AutomaticAdjustment"."AdjustmentType" = "AdjustmentType"."Id")
		join "CompanyPlanType" on ( "CompanyPlanType"."Id" = "AutomaticAdjustment"."PlanTypeId" )
		join "RetroData" on ("RetroData"."Id" = "AutomaticAdjustment"."RetroDataId" )
		join "CompanyCarrier" on ( "CompanyCarrier"."Id" = "CompanyPlanType"."CarrierId" )
	where
		1=1
	and "CompanyPlanType"."PlanAnniversaryMonth" is not null
	and "AdjustmentType"."Id" = 2
	and "AutomaticAdjustment"."CompanyId" = ?
	and "AutomaticAdjustment"."ImportDate" = ?
	and "AutomaticAdjustment"."TargetDate" < ?
	and "AutomaticAdjustment"."CarrierId" = ?
	and "AutomaticAdjustment"."PlanTypeId" = ?
	and "AutomaticAdjustment"."PlanId" = ?
	and "AutomaticAdjustment"."CoverageTierId" = ?
