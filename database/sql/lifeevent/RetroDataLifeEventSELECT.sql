select
	"CompanyLife"."FirstName"
	, "CompanyLife"."LastName"
	, "CompanyLife"."EmployeeId"
	, "CompanyCarrier"."UserDescription" as "Carrier"
	, "CompanyPlanType"."UserDescription" as "PlanType"
	, "CompanyPlan"."UserDescription" as "Plan"
	, "RetroDataLifeEvent"."Before-CoverageStartDateList" as "BeforeCoverageStartDateList"
    , TO_CHAR("RetroDataLifeEvent"."CoverageStartDate" + interval '0 month', 'mm/dd/yyyy') as "CoverageStartDate"
    , "RetroDataLifeEvent"."LifeEvent"
    , "RetroDataLifeEvent"."Id" as "RetroDataLifeEventId"
    , "RetroDataLifeEvent"."Before-CoverageStartDateList" as "BeforeCoverageStartDateList"
	, "CompanyPlanType"."RetroRule"
from
	"RetroDataLifeEvent"
	join "RetroData" on ("RetroData"."Id" = "RetroDataLifeEvent"."RetroDataId" )
	join "CompanyLife" on ( "CompanyLife"."CompanyId" = "RetroDataLifeEvent"."CompanyId" and "CompanyLife"."Id" = "RetroData"."LifeId" )
	join "CompanyCarrier" on ( "CompanyCarrier"."Id" = "RetroData"."CarrierId" )
	join "CompanyPlanType" on ( "CompanyPlanType"."Id" = "RetroData"."PlanTypeId" )
	join "CompanyPlan" on ( "CompanyPlan"."Id" = "RetroData"."PlanId" )
where
	1=1
	and "RetroDataLifeEvent"."CompanyId" = ?
	and "RetroDataLifeEvent"."ImportDate" = ?
	and "RetroDataLifeEvent"."AutoSelected" = false
order by
	"CompanyLife"."FirstName"
	, "CompanyLife"."LastName"
	, "CompanyLife"."EmployeeId"
	, "CompanyCarrier"."UserDescription"
	, "CompanyPlanType"."UserDescription"
	, "CompanyPlan"."UserDescription"
