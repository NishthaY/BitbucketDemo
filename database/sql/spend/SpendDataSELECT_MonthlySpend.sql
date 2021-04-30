select
	coalesce(sum("Premium"), 0) as "MonthlySpend"
from
    "SummaryData"
where
    "CompanyId" = ?
    and "ImportDate" = ?
