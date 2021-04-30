select
	"Issue"
from
	"ReportReviewWarnings"
where
	"ReportReviewWarnings"."CompanyId" = ?
	and "ReportReviewWarnings"."ImportDate" = ?
	and "ReportReviewWarnings"."Confirm" = true
group by "Issue"