select
    "CompanyReport".*
    , "ReportType"."Name" as "ReportTypeCode"
from
    "CompanyReport"
    join "ReportType" on ( "ReportType"."Id" = "CompanyReport"."ReportTypeId" )
where
    "CompanyReport"."CompanyId" = ?
    and "CompanyReport"."Id" = ?
