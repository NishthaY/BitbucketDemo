select "CompanyId", "ImportDate", "CarrierId", "CarrierDescription", "PremiumEquivalentFlg" from
(
	-- by import data
	select
		"ImportData"."CompanyId"
		, "ImportData"."ImportDate"
		, "CompanyCarrier"."Id" as "CarrierId"
		, "CompanyCarrier"."UserDescription" as "CarrierDescription"
		, false as "PremiumEquivalentFlg"
	from
		"ImportData"
		join "WashedData" on ( "WashedData"."ImportDataId" = "ImportData"."Id" )
		join "CompanyCarrier" on
		(
			"CompanyCarrier"."CompanyId" = "ImportData"."CompanyId"
			and "CompanyCarrier"."CarrierNormalized" = upper("ImportData"."Carrier")
		)

	where
		"ImportData"."CompanyId" = ?
		and "ImportData"."ImportDate" = ?
		and "ImportData"."Finalized" = false
		and "WashedData"."WashedOutFlg" = false

	UNION ALL

	-- by automatic adjustment
	select
		"AutomaticAdjustment"."CompanyId"
		, "AutomaticAdjustment"."ImportDate"
		, "CompanyCarrier"."Id" as "CarrierId"
		, "CompanyCarrier"."UserDescription" as "CarrierDescription"
		, false as "PremiumEquivalentFlg"
	from
		"AutomaticAdjustment"
		join "CompanyCarrier" on
		(
			"CompanyCarrier"."CompanyId" = "AutomaticAdjustment"."CompanyId"
			and "CompanyCarrier"."Id" = "AutomaticAdjustment"."CarrierId"
		)
	where
		"AutomaticAdjustment"."CompanyId" = ?
		and "AutomaticAdjustment"."ImportDate" = ?

	UNION ALL

	-- by Premium equivalent
	select
		pe."CompanyId"
		, pe."ImportDate"
		, pe."CarrierId" as "CarrierId"
		, format('%s Premium Equivalent', "CompanyCarrier"."UserDescription") as "CarrierDescription"
		, true as "PremiumEquivalentFlg"
	from
		"SummaryDataPremiumEquivalent" pe
		join "CompanyCarrier" on
		(
			"CompanyCarrier"."CompanyId" = pe."CompanyId"
			and "CompanyCarrier"."Id" = pe."CarrierId"
		)
	where
		pe."CompanyId" = ?
		and pe."ImportDate" = ?

) as tbl
group by tbl."CompanyId", tbl."ImportDate", tbl."CarrierId", tbl."CarrierDescription", tbl."PremiumEquivalentFlg"
order by tbl."CarrierDescription" asc
