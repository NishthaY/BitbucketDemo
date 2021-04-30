select
	TO_CHAR("ImportDate" + interval '0 month', 'Month YYYY') as "RecentMonthYYYY"
	, TO_CHAR("ImportDate" + interval '0 month', 'mm/dd/yyyy') as "RecentMMDDYYYY"
    , TO_CHAR("ImportDate", 'Month' ) as "RecentMonth"
	, TO_CHAR("ImportDate", 'Mon' ) as "RecentMon"

from "ImportData" where "CompanyId" = ? and "Finalized" = true group by "ImportDate" order by "ImportDate" desc limit 1
