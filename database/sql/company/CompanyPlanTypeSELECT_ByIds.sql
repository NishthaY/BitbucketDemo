select
	"CompanyPlanType"."Id"
	, "CompanyPlanType"."CompanyId"
	, "CompanyPlanType"."CarrierId"
	, "CompanyPlanType"."PlanTypeNormalized"
	, "CompanyPlanType"."UserDescription"
	, "CompanyPlanType"."PlanTypeCode"
	, "CompanyPlanType"."RetroRule"
	, "CompanyPlanType"."WashRule"
	, "CompanyPlanType"."PlanAnniversaryMonth"
	, "CompanyPlanType"."Ignored"
	, "CompanyCarrier"."CarrierNormalized"
from
	"CompanyPlanType"
	join "CompanyCarrier" on ( "CompanyPlanType"."CarrierId" = "CompanyCarrier"."Id" )
where
	"CompanyPlanType"."CompanyId" = ?
	and "CompanyCarrier"."Id" = ?
	and "CompanyPlanType"."Id" = ?
