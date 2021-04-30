select "CompanyId", "ImportDate", "CarrierId", "Carrier", "Total", "Adjustments", "BalanceDue", "PremiumEquivalent" from
(
	select
		"SummaryData"."CompanyId"
		, "SummaryData"."ImportDate"
		, "CompanyCarrier"."Id" as "CarrierId"
		, "CompanyCarrier"."UserDescription" as "Carrier"
		, sum("SummaryData"."Premium") as "Total"
		, sum("SummaryData"."AdjustedPremium") as "Adjustments"
		, sum("SummaryData"."Premium") + sum("SummaryData"."AdjustedPremium") as "BalanceDue"
		, false as "PremiumEquivalent"
	from
		"SummaryData"
		join "CompanyCarrier" on ( "CompanyCarrier"."Id" = "SummaryData"."CarrierId" )
	where
		"SummaryData"."CompanyId" = ?
		and "SummaryData"."ImportDate" = ?
	group by "SummaryData"."CompanyId", "SummaryData"."ImportDate", "CompanyCarrier"."UserDescription", "CompanyCarrier"."Id"

	UNION ALL

	select
		"SummaryDataPremiumEquivalent"."CompanyId"
		, "SummaryDataPremiumEquivalent"."ImportDate"
		, "CompanyCarrier"."Id" as "CarrierId"
		, format('%s Premium Equivalent', "CompanyCarrier"."UserDescription") as "Carrier"
		, sum("SummaryDataPremiumEquivalent"."Premium") as "Total"
		, sum("SummaryDataPremiumEquivalent"."AdjustedPremium") as "Adjustments"
		, sum("SummaryDataPremiumEquivalent"."Premium") + sum("SummaryDataPremiumEquivalent"."AdjustedPremium") as "BalanceDue"
		, true as "PremiumEquivalent"
	from
		"SummaryDataPremiumEquivalent"
		join "CompanyCarrier" on ( "CompanyCarrier"."Id" = "SummaryDataPremiumEquivalent"."CarrierId" )
	where
		"SummaryDataPremiumEquivalent"."CompanyId" = ?
		and "SummaryDataPremiumEquivalent"."ImportDate" = ?

	group by "SummaryDataPremiumEquivalent"."CompanyId", "SummaryDataPremiumEquivalent"."ImportDate", "CompanyCarrier"."UserDescription", "CompanyCarrier"."Id"

) t
where
	1=1
	--and ( t."Total" <> 0 OR t."Adjustments" <> 0 OR t."BalanceDue" <> 0 )
order by "Carrier" asc
