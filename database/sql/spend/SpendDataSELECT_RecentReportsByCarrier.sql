select "CarrierId", "Carrier", "Spend", "WashRetroCatch", "SummaryReportId", "DetailReportId" from (
	select
		"CompanyCarrier"."Id" as "CarrierId"
		,"CompanyCarrier"."UserDescription" as "Carrier"
		, sum(coalesce("SummaryData"."Premium",0)) as "Spend"
		, sum(coalesce("SummaryData"."AdjustedPremium",0))as "WashRetroCatch"
		, summary_cr."Id" as "SummaryReportId"
		, detail_cr."Id" as "DetailReportId"
	from
		"SummaryData"
		join "CompanyCarrier" on ( "CompanyCarrier"."Id" = "SummaryData"."CarrierId" )
		left join "CompanyReport" summary_cr on (
			summary_cr."CompanyId" = "SummaryData"."CompanyId"
			and summary_cr."ImportDate" = "SummaryData"."ImportDate"
			and summary_cr."CarrierId" = "SummaryData"."CarrierId"
			and summary_cr."ReportTypeId" = 1
		)
		left join "CompanyReport" detail_cr on (
			detail_cr."CompanyId" = "SummaryData"."CompanyId"
			and detail_cr."ImportDate" = "SummaryData"."ImportDate"
			and detail_cr."CarrierId" = "SummaryData"."CarrierId"
			and detail_cr."ReportTypeId" = 2
		)
	where
		"SummaryData"."CompanyId" = ?
		and "SummaryData"."ImportDate" = ?
		and "SummaryData"."PlanTypeId" is not null		-- Exclude Manual Adjustments until they are bound a benifit
	group by "SummaryData"."CompanyId", "SummaryData"."ImportDate", "CompanyCarrier"."Id", "CompanyCarrier"."UserDescription", summary_cr."Id", detail_cr."Id"

	UNION ALL

	select
		"CompanyCarrier"."Id" as "CarrierId"
		, format('%s Premium Equivalent', "CompanyCarrier"."UserDescription") as "Carrier"
		, sum(coalesce("SummaryDataPremiumEquivalent"."Premium",0)) as "Spend"
		, sum(coalesce("SummaryDataPremiumEquivalent"."AdjustedPremium",0))as "WashRetroCatch"
		, summary_cr."Id" as "SummaryReportId"
		, detail_cr."Id" as "DetailReportId"
	from
		"SummaryDataPremiumEquivalent"
		join "CompanyCarrier" on ( "CompanyCarrier"."Id" = "SummaryDataPremiumEquivalent"."CarrierId" )
		left join "CompanyReport" summary_cr on (
			summary_cr."CompanyId" = "SummaryDataPremiumEquivalent"."CompanyId"
			and summary_cr."ImportDate" = "SummaryDataPremiumEquivalent"."ImportDate"
			and summary_cr."CarrierId" = "SummaryDataPremiumEquivalent"."CarrierId"
			and summary_cr."ReportTypeId" = 3
		)
		left join "CompanyReport" detail_cr on (
			detail_cr."CompanyId" = "SummaryDataPremiumEquivalent"."CompanyId"
			and detail_cr."ImportDate" = "SummaryDataPremiumEquivalent"."ImportDate"
			and detail_cr."CarrierId" = "SummaryDataPremiumEquivalent"."CarrierId"
			and detail_cr."ReportTypeId" = 4
		)
	where
		"SummaryDataPremiumEquivalent"."CompanyId" = ?
		and "SummaryDataPremiumEquivalent"."ImportDate" = ?
		and "SummaryDataPremiumEquivalent"."PlanTypeId" is not null		-- Exclude Manual Adjustments until they are bound a benifit
	group by "SummaryDataPremiumEquivalent"."CompanyId", "SummaryDataPremiumEquivalent"."ImportDate", "CompanyCarrier"."Id", "CompanyCarrier"."UserDescription", summary_cr."Id", detail_cr."Id"
) as t
order by t."Carrier" asc
