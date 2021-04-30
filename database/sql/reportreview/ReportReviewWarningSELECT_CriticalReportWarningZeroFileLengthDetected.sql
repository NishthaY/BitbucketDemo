select *
from
    "ReportReviewWarnings"
where
    "CompanyId" = ?
    and "ImportDate" = ?
    and "Confirm" = true
    and "Issue" like '{REPORT_NAME} not generated. Zero file length detected.'