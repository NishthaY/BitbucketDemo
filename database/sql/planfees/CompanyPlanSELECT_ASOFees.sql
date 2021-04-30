select
	"CompanyPlan"."CarrierId"
	, "CompanyPlan"."PlanTypeId"
	, "CompanyPlan"."ASOFee"
	, "CompanyPlan"."ASOFeeCarrierId" as "NewCarrierId"
	, "CompanyPlan"."ASOFeePlanTypeId" as "NewPlanTypeId"
	, "CompanyCarrier"."UserDescription" as "NewCarrier"
	, "CompanyPlanType"."UserDescription" as "NewPlanType"
	, "PlanTypes"."PlanFees" as "SupportsFee"
	, parent_plantype."PlanTypeCode" as "ParentPlanTypeCode"
from
	"CompanyPlan"
	join "CompanyCarrier" on ( "CompanyCarrier"."CompanyId" = "CompanyPlan"."CompanyId" and "CompanyCarrier"."Id" = "CompanyPlan"."ASOFeeCarrierId" )
	join "CompanyPlanType" on ( "CompanyPlanType"."CompanyId" = "CompanyPlan"."CompanyId" and "CompanyPlanType"."Id" = "CompanyPlan"."ASOFeePlanTypeId" )
	join "CompanyPlanType" parent_plantype on ( parent_plantype."Id" = "CompanyPlan"."PlanTypeId" )
	join "PlanTypes" on ( parent_plantype."PlanTypeCode" = "PlanTypes"."Name" )

where
	"CompanyPlan"."CompanyId" = ?
	and "CompanyPlan"."ASOFee" is not null
	and "CompanyPlan"."ASOFeeCarrierId" is not null
	and "CompanyPlan"."ASOFeePlanTypeId" is not null
	and parent_plantype."Ignored" = false
group by
	"CompanyPlan"."CarrierId"
	, "CompanyPlan"."PlanTypeId"
	, "CompanyPlan"."ASOFee"
	, "CompanyPlan"."ASOFeeCarrierId"
	, "CompanyPlan"."ASOFeePlanTypeId"
	, "CompanyCarrier"."UserDescription"
	, "CompanyPlanType"."UserDescription"
	, "PlanTypes"."PlanFees"
	, parent_plantype."PlanTypeCode"
