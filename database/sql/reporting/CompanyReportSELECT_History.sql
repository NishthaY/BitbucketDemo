select display_date, import_date, carrier, carrier_id, report_type_id, summary_report_id, detail_report_id, draft_flg, company_id, sort_date, report_name, report_code from
(
	select
		TO_CHAR(r1."ImportDate", 'Month YYYY') as display_date
		, r1."ImportDate" as import_date
		, "CompanyCarrier"."UserDescription" as carrier
		, "CompanyCarrier"."Id" as carrier_id
		, r1."ReportTypeId" as report_type_id
		, r1."Id" as summary_report_id
		, r2."Id" as detail_report_id
		, false as draft_flg
		, r1."CompanyId" as company_id
		, r1."ImportDate" as sort_date
		, "ReportType"."Display" as report_name
		, "ReportType"."Name" as report_code
	from
		"CompanyReport" as r1
		join "ReportType" on ( "ReportType"."Id" = r1."ReportTypeId" )
		join "CompanyCarrier" on ("CompanyCarrier"."Id" = r1."CarrierId" )
		left join "CompanyReport" as r2 on
		(
			r1."CompanyId" = r2."CompanyId"
			and r1."ImportDate" = r2."ImportDate"
			and r1."CarrierId" = r2."CarrierId"
		)
	where
		r1."CompanyId" = ?
		and r1."ReportTypeId" = 1
		and r2."ReportTypeId" = 2
		and r1."ImportDate" <> ?

	UNION ALL

	select
		TO_CHAR(r1."ImportDate", 'Month YYYY') as display_date
		, r1."ImportDate"
		, format('%s Premium Equivalent', "CompanyCarrier"."UserDescription") as carrier
		, "CompanyCarrier"."Id" as carrier_id
		, r1."ReportTypeId" as report_type_id
		, r1."Id" as summary_report_id
		, r2."Id" as detail_report_id
		, false as draft_flg
		, r1."CompanyId" as company_id
		, r1."ImportDate" as sort_date
		, "ReportType"."Display" as report_name
		, "ReportType"."Name" as report_code
	from
		"CompanyReport" as r1
		join "ReportType" on ( "ReportType"."Id" = r1."ReportTypeId" )
		join "CompanyCarrier" on ("CompanyCarrier"."Id" = r1."CarrierId" )
		left join "CompanyReport" as r2 on
		(
			r1."CompanyId" = r2."CompanyId"
			and r1."ImportDate" = r2."ImportDate"
			and r1."CarrierId" = r2."CarrierId"
		)
	where
		r1."CompanyId" = ?
		and r1."ReportTypeId" = 3
		and r2."ReportTypeId" = 4
		and r1."ImportDate" <> ?

	UNION ALL

	(
		SELECT
			TO_CHAR("ReportReviewWarnings"."ImportDate", 'Month YYYY') 			AS display,
			"ReportReviewWarnings"."ImportDate"                        			AS import_date,
			NULL                                                       			AS carrier,
			NULL                                                       			AS carrier_id,
			( select "Id" from "ReportType" where "Name" = 'issues') 				AS report_type_id,
			NULL                                                       			AS summary_report_id,
			NULL                                                       			AS detail_report_id,
			TRUE                                                       			AS draft_flg,
			"ReportReviewWarnings"."CompanyId"                         			AS company_id,
			"ReportReviewWarnings"."ImportDate" 														AS sort_date,
			( select "Display" from "ReportType" where "Name" = 'issues' )	AS report_name,
			'issues' 																												AS report_code
		FROM "ReportReviewWarnings"
		WHERE "CompanyId" = ? AND "ImportDate" <> ?
		GROUP BY "ReportReviewWarnings"."CompanyId", "ReportReviewWarnings"."ImportDate"
	)


) as t
order by sort_date desc, carrier asc
