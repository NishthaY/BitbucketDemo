select
	TO_CHAR("ImportDate", 'Month YYYY') as "UploadDisplayMonth"
	, TO_CHAR("ImportDate", 'mm/dd/yyyy') as "UploadMonth"

from "ImportData" where "CompanyId" = ? and "Finalized" = true group by "ImportDate" order by "ImportDate" desc limit 1
