select
	"CompanyPlan"."CarrierId"
	, "CompanyPlan"."PlanTypeId"
	, "CompanyPlan"."StopLossFee"
	, "CompanyPlan"."StopLossFeeCarrierId" as "NewCarrierId"
	, "CompanyPlan"."StopLossFeePlanTypeId" as "NewPlanTypeId"
	, "CompanyCarrier"."UserDescription" as "NewCarrier"
	, "CompanyPlanType"."UserDescription" as "NewPlanType"
	, "PlanTypes"."PlanFees" as "SupportsFee"
	, parent_plantype."PlanTypeCode" as "ParentPlanTypeCode"
from
	"CompanyPlan"
	join "CompanyCarrier" on ( "CompanyCarrier"."CompanyId" = "CompanyPlan"."CompanyId" and "CompanyCarrier"."Id" = "CompanyPlan"."StopLossFeeCarrierId" )
	join "CompanyPlanType" on ( "CompanyPlanType"."CompanyId" = "CompanyPlan"."CompanyId" and "CompanyPlanType"."Id" = "CompanyPlan"."StopLossFeePlanTypeId" )
	join "CompanyPlanType" parent_plantype on ( parent_plantype."Id" = "CompanyPlan"."PlanTypeId" )
	join "PlanTypes" on ( parent_plantype."PlanTypeCode" = "PlanTypes"."Name" )

where
	"CompanyPlan"."CompanyId" = ?
	and "CompanyPlan"."StopLossFee" is not null
	and "CompanyPlan"."StopLossFeeCarrierId" is not null
	and "CompanyPlan"."StopLossFeePlanTypeId" is not null
	and parent_plantype."Ignored" = false
group by
	"CompanyPlan"."CarrierId"
	, "CompanyPlan"."PlanTypeId"
	, "CompanyPlan"."StopLossFee"
	, "CompanyPlan"."StopLossFeeCarrierId"
	, "CompanyPlan"."StopLossFeePlanTypeId"
	, "CompanyCarrier"."UserDescription"
	, "CompanyPlanType"."UserDescription"
	, "PlanTypes"."PlanFees"
	, parent_plantype."PlanTypeCode"
