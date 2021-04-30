delete from "ReportReviewWarnings" r
using "CompanyCommissionWarning" c
where
  r."CompanyId" = ?
  and r."ImportDate" = ?
  and c."CompanyId" = r."CompanyId"
  and c."ImportDate" = r."ImportDate"
  and r."Issue" = c."Issue"