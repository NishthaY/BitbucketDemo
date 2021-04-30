select case when count("MappedFlg") > 0 then false else true end as "EverythingMappedFlg" from (
	select
		CASE
			WHEN ( coalesce("CompanyPlanType"."PlanTypeCode", '') <> '' ) THEN true
			WHEN ( "CompanyPlanType"."Ignored" = true ) THEN true
			ELSE false
		END as "MappedFlg"
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
	group by
		"ImportData"."Carrier", "ImportData"."PlanType", coalesce("CompanyPlanType"."PlanTypeCode", ''), "CompanyPlanType"."Ignored"
) as "tbl" where "tbl"."MappedFlg" = false
