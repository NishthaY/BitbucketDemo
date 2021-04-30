select
    TO_CHAR("ImportDate", 'mm/dd/YYYY') as "InitialImportDate"
     , TO_CHAR("ImportDate" + interval '0 month', 'Month YYYY') as "Display"
     , TO_CHAR("ImportDate" + interval '0 month', 'YYYYmm') as "DateTag"
     , TO_CHAR("ImportDate" + interval '0 month', 'Mon YYYY') as "ShortDate"
from
    "SummaryData"
where
    "CompanyId" = ?
    and "ImportDate" <= ?
order by "ImportDate" asc limit 1