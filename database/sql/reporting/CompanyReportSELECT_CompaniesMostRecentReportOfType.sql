select
  "CompanyReport".*
from
  "CompanyReport"
  join "ReportType" on ( "ReportType"."Id" = "CompanyReport"."ReportTypeId")
where
  "CompanyReport"."CompanyId" = ?
  and "ReportType"."Name" = ?
order by "CompanyReport"."ImportDate" desc
limit 1