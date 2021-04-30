insert into "ReportReviewWarnings" ( "CompanyId", "ImportDate", "ImportDataId", "Issue")
select
  "CompanyId"
  , "ImportDate"
  , coalesce ( "ImportDataId", 0 )
  , CASE WHEN "Internal" = false then "Issue" ELSE 'Unable to process commissions for life.' END as "Issue"
from
  "CompanyCommissionWarning"
where
  "CompanyId" = ?
  and "ImportDate" = ?