select
	"ImportData"."EmployeeSSNDisplay" as "Employee SSN"
	, "ImportData"."FirstName" as "First Name"
	, "ImportData"."LastName" as "Last Name"
	, "ImportData"."RowNumber" as "Row Number"
	, "ReportReviewWarnings"."Issue"
from
	"ReportReviewWarnings"
	left join "ImportData" on ("ReportReviewWarnings"."ImportDataId" = "ImportData"."Id")
where
	"ReportReviewWarnings"."CompanyId" = ?
	and "ReportReviewWarnings"."ImportDate" = ?