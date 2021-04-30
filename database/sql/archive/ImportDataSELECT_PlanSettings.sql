select
	"CompanyCarrier"."UserDescription" as "Carrier"
	, "CompanyPlanType"."UserDescription" as "PlanType"
	, "CompanyPlan"."UserDescription" as "Plan"
	, "CompanyCoverageTier"."UserDescription" as "CoverageTier"
	, CASE WHEN ( "CompanyPlanType"."Ignored" is null ) THEN false ELSE "CompanyPlanType"."Ignored" END as "PlanTypeIgnored"
	, coalesce("CompanyPlanType"."PlanTypeCode", '') as "PlanTypeCode"
	, "CompanyCarrier"."Id" as "CarrierId"
	, "CompanyPlanType"."Id" as "PlanTypeId"
	, "CompanyPlan"."Id" as "PlanId"
	, "CompanyCoverageTier"."Id" as "CoverageTierId"
from
	"ImportData"
	left join "CompanyCarrier" 	 on ( "CompanyCarrier"."CompanyId" = "ImportData"."CompanyId" and "CompanyCarrier"."CarrierNormalized" = upper("ImportData"."Carrier") )
	left join "CompanyPlanType" 	 on ( "CompanyPlanType"."CarrierId" = "CompanyCarrier"."Id" and upper("ImportData"."PlanType") = "CompanyPlanType"."PlanTypeNormalized"  )
	left join "CompanyPlan" 	 on ( "CompanyPlan"."PlanTypeId" = "CompanyPlanType"."Id" and "CompanyPlan"."PlanNormalized" = upper("ImportData"."Plan") )
	left join "CompanyCoverageTier" on ( "CompanyCoverageTier"."PlanId" = "CompanyPlan"."Id" and "CompanyCoverageTier"."CoverageTierNormalized" = upper("ImportData"."CoverageTier" )  )
	left join "PlanTypes" 		 on ( "PlanTypes"."Name" = "CompanyPlanType"."PlanTypeCode" )
where
	"ImportData"."CompanyId" = ?
	and "ImportData"."Finalized" = false

group by
	"CompanyCarrier"."UserDescription", "CompanyCarrier"."Id", "CompanyPlanType"."UserDescription", "CompanyPlanType"."Id", coalesce("CompanyPlanType"."PlanTypeCode", ''), "CompanyPlan"."UserDescription" , "CompanyPlan"."Id", "CompanyCoverageTier"."UserDescription", "CompanyCoverageTier"."Id", "CompanyPlanType"."Ignored"
order by
	"CompanyCarrier"."UserDescription" asc, "CompanyPlanType"."UserDescription" asc, coalesce("CompanyPlanType"."PlanTypeCode", '') asc, "CompanyPlan"."UserDescription" asc, "CompanyCoverageTier"."UserDescription" asc
