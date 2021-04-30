select case when count("Ignored") > 0 then false else true end as "EverythingIgnoredFlg" from (
	select
		"CompanyPlanType"."Ignored"
	from
		"ImportData"
		left join "CompanyCarrier" on
		(
			"CompanyCarrier"."CompanyId" = "ImportData"."CompanyId"
			and "CompanyCarrier"."CarrierNormalized" = upper("ImportData"."Carrier")
		)
		left join "CompanyPlanType" on
		(
			"CompanyPlanType"."CarrierId" = "CompanyCarrier"."Id"
			and "CompanyPlanType"."PlanTypeNormalized" = upper("ImportData"."PlanType")
		)
	where
		"ImportData"."CompanyId" = ?
		and "ImportData"."Finalized" = false
	group by "CompanyPlanType"."CarrierId", "CompanyPlanType"."Id", "CompanyPlanType"."Ignored"
) as "tbl" where "tbl"."Ignored" = false
