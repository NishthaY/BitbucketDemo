select
	"Issue"
from
	"ReportReviewWarnings"
where
	"ReportReviewWarnings"."CompanyId" = ?
	and "ReportReviewWarnings"."ImportDate" = ?
	and "ReportReviewWarnings"."Confirm" = false
group by "Issue"