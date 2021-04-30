delete from "CompanyReport" where "Id" in (
  select
    "CompanyReport"."Id"
  from
    "CompanyReport"
    join "ReportType" on ( "ReportType"."Id" = "CompanyReport"."ReportTypeId")
  where
    "CompanyReport"."CompanyId" = ?
    and "CompanyReport"."ImportDate" = ?
    and "ReportType"."Name" = ?
)
