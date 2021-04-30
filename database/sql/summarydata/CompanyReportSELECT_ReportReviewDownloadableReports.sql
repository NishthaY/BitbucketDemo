select

    "CompanyReport"."Id" as "ReportId"
  , "ReportType"."Id" as "ReportTypeId"
  , "ReportType"."Display" as "ReportDisplay"
  , "ReportType"."Name" as "ReportCode"

  ,case
   when "ReportType"."Name" = 'summary' then 1
   when "ReportType"."Name" = 'pe_summary' then 2
   when "ReportType"."Name" = 'detail' then 3
   when "ReportType"."Name" = 'pe_detail' then 4
   when "ReportType"."Name" = 'commission' then 5
   when "ReportType"."Name" = 'issues' then 6
   when "ReportType"."Name" = 'transamerica_actuarial' then 7
   when "ReportType"."Name" = 'transamerica_eligibility' then 8
   when "ReportType"."Name" = 'transamerica_commission' then 9
   else 99
   end as "SortOrder"


from
  "ReportType"
  left join "CompanyReport" on (
  "CompanyReport"."CompanyId" = ?
  and "CompanyReport"."CarrierId" = ?
  and "CompanyReport"."ImportDate" = ?
  and "ReportType"."Id" = "CompanyReport"."ReportTypeId"

  )
order by "SortOrder" asc, "ReportDisplay" asc