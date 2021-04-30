select
    TO_CHAR("ImportDate" + interval '0 month', 'Month YYYY') as "ImportMonthYYYY"
  , TO_CHAR("ImportDate" + interval '0 month', 'mm/dd/yyyy') as "ImportMMDDYYYY"
  , TO_CHAR("ImportDate", 'Month' ) as "ImportMonth"
  , TO_CHAR("ImportDate", 'Mon' ) as "ImportMon"

from "ImportData" where "CompanyId" = ? and "Finalized" = false group by "ImportDate" order by "ImportDate" desc limit 1
