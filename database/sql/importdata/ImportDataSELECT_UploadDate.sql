select
	TO_CHAR("ImportDate" + interval '1 month', 'Month YYYY') as "UploadDisplayMonth"
	, TO_CHAR("ImportDate" + interval '1 month', 'mm/dd/yyyy') as "UploadMonth"
    , TO_CHAR("ImportDate" + interval '1 month', 'Mon YYYY') as "UploadDisplayMonthShort"

from "ImportData" where "CompanyId" = ? and "Finalized" = true group by "ImportDate" order by "ImportDate" desc limit 1
