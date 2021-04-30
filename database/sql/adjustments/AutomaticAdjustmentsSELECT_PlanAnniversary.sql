-- This query will return the full carrier,plantype,plan,coveragetier key
-- along with the plan anniversary month.  No duplicates.  Will not return
-- items that do not have a plan anniversary set.
select
	"AutomaticAdjustment"."CarrierId"
	, "AutomaticAdjustment"."PlanTypeId"
	, "AutomaticAdjustment"."PlanId"
	, "AutomaticAdjustment"."CoverageTierId"
	, "CompanyPlanType"."PlanAnniversaryMonth"
from
	"AutomaticAdjustment"
	join "CompanyPlanType" on ( "CompanyPlanType"."Id" = "AutomaticAdjustment"."PlanTypeId" )
where
	"AutomaticAdjustment"."CompanyId" = ?
	and "AutomaticAdjustment"."ImportDate" = ?
	and "CompanyPlanType"."PlanAnniversaryMonth" is not null
	and "CompanyPlanType"."PlanAnniversaryMonth" <> 0
group by
	"AutomaticAdjustment"."CarrierId"
	, "AutomaticAdjustment"."PlanTypeId"
	, "AutomaticAdjustment"."PlanId"
	, "AutomaticAdjustment"."CoverageTierId"
	, "CompanyPlanType"."PlanAnniversaryMonth"
