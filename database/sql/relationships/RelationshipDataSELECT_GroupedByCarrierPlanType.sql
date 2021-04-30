select
	"CompanyCarrier"."Id" as "CarrierId"
	, "CompanyPlanType"."PlanTypeCode" as "PlanTypeCode"
from
	"RelationshipData"
	join "ImportData" on ( "ImportData"."Id" = "RelationshipData"."ImportDataId" )
	join "CompanyCarrier" on
	(
		"CompanyCarrier"."CompanyId" = "ImportData"."CompanyId"
		and "CompanyCarrier"."CarrierNormalized" = upper("ImportData"."Carrier")
	)
	join "CompanyPlanType" on
 	(
 		"CompanyPlanType"."CarrierId" =  "CompanyCarrier"."Id"
 		and "CompanyPlanType"."PlanTypeNormalized" = upper("ImportData"."PlanType")
 	)
where
	"RelationshipData"."CompanyId" = ?
	and "RelationshipData"."ImportDate" = ?
	and "RelationshipData"."RelationshipCode" = 'dependent'
group by "CompanyCarrier"."Id", "CompanyPlanType"."PlanTypeCode"
