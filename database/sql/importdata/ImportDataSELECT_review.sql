select
	"CompanyCarrier"."UserDescription" as "Carrier"
	, "CompanyPlanType"."UserDescription" as "PlanType"
	, coalesce("CompanyPlanType"."PlanTypeCode", '') as "PlanTypeCode"
	, "CompanyPlan"."UserDescription" as "Plan"
	, "CompanyCoverageTier"."UserDescription" as "CoverageTier"
	, "CompanyCoverageTier"."Id" as "CoverageTierId"
	, CASE WHEN ( "CompanyPlanType"."Ignored" is null ) THEN false ELSE "CompanyPlanType"."Ignored" END as "Ignored"
	, CASE WHEN ( coalesce("CompanyPlanType"."PlanTypeCode", '') <> '' ) THEN true ELSE false END as "MappedFlg"
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
	and ( "CompanyPlanType"."PlanTypeCode" is null or "CompanyPlanType"."PlanTypeCode" not like '%_aso' )
	and ( "CompanyPlanType"."PlanTypeCode" is null or "CompanyPlanType"."PlanTypeCode" not like '%_stoploss' )
group by
	"CompanyCarrier"."UserDescription", "CompanyPlanType"."UserDescription", coalesce("CompanyPlanType"."PlanTypeCode", ''), "CompanyPlan"."UserDescription" , "CompanyCoverageTier"."UserDescription", "CompanyCoverageTier"."Id", "CompanyPlanType"."Ignored"
order by
	"CompanyCarrier"."UserDescription" asc, "CompanyPlanType"."UserDescription" asc, coalesce("CompanyPlanType"."PlanTypeCode", '') asc, "CompanyPlan"."UserDescription" asc, "CompanyCoverageTier"."UserDescription" asc
