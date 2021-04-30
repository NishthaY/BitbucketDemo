select
  "ImportData"."EmployeeId"
from
  "ReportReviewWarnings"
  left join "ImportData" on ("ReportReviewWarnings"."ImportDataId" = "ImportData"."Id")
where
  "ReportReviewWarnings"."CompanyId" = ?
  and "ReportReviewWarnings"."ImportDate" = ?
limit 1
