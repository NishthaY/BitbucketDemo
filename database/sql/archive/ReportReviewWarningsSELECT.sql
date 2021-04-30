select
	"Issue"
	, "ImportData"."EmployeeId"
	, "ImportData"."FirstName"
	, "ImportData"."LastName"
	, "ImportData"."RowNumber"	
	, "ImportDataId"
from
	"ReportReviewWarnings"
	left join "ImportData" on ("ImportData"."Id" = "ReportReviewWarnings"."ImportDataId")
where
	"ReportReviewWarnings"."CompanyId" = ?
	and "ReportReviewWarnings"."ImportDate" = ?
