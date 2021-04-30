delete from "ReportReviewWarnings" r
using "LifeOriginalEffectiveDateWarning" w
where
  r."CompanyId" = ?
  and r."ImportDate" = ?
  and w."CompanyId" = r."CompanyId"
  and w."ImportDate" = r."ImportDate"
  and r."Issue" = w."Issue"