select
      "CompanyReport"."Id" as "ReportId"
    , "Company"."CompanyName"
    , "CompanyCarrier"."Id" as "CarrierId"
    , "CompanyCarrier"."UserDescription" as "Carrier"
    , "ReportType"."Name" as "ReportTypeCode"
    , to_char("CompanyReport"."ImportDate",'yyyymm') as "ReportDate"
    , "CompanyCarrier"."CarrierCode"
    , "Company"."Id" as "CompanyId"
    , "CompanyReport"."ImportDate"
from
    "CompanyReport"
    join "ReportType" on ( "ReportType"."Id" = "CompanyReport"."ReportTypeId" )
    join "Company" on ("Company"."Id" = "CompanyReport"."CompanyId")
    join "CompanyCarrier" on ( "CompanyCarrier"."Id" = "CompanyReport"."CarrierId" )
where
    "CompanyReport"."Id" = ?