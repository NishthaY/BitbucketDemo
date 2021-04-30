select
    DISTINCT("ImportDate")
           , TO_CHAR("ImportDate" + interval '0 month', 'Month YYYY') as "Display"
           , TO_CHAR("ImportDate" + interval '0 month', 'YYYYmm') as "DateTag"
           , TO_CHAR("ImportDate" + interval '0 month', 'Mon YYYY') as "ShortDate"
from
    "SummaryData"
where
        "CompanyId" = ?
order by "ImportDate" desc;
