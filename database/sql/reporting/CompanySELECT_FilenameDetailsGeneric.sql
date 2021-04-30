select
    "Company"."Id" as "CompanyId"
  , "Company"."CompanyName"
  , to_char(date(?) ,'yyyymm') as "ReportDate"
from "Company"
where "Company"."Id" = ?