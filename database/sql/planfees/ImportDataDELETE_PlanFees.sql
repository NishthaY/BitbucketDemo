delete from "ImportData" where "Id" in (
	select
		"ImportData"."Id"
	from
		"ImportData"
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
		"ImportData"."CompanyId" = ?
		and "ImportDate" = ?
		and ( "CompanyPlanType"."PlanTypeCode" like '%_aso' OR "CompanyPlanType"."PlanTypeCode" like '%_stoploss' )
)
