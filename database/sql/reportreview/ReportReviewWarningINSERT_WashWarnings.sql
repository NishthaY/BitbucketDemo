insert into "ReportReviewWarnings" ( "CompanyId", "ImportDataId", "ImportDate", "Issue" )
select
	"WashedData"."CompanyId"
	, "WashedData"."ImportDataId"
	, "WashedData"."ImportDate"
	, trim(leading 'WARNING:' from "WashedData"."WashDescription" )
from
	"WashedData"
where
	"CompanyId" = ?
	and "ImportDate" = ?
	and "WashDescription" like 'WARNING:%'
