select
	sum(coalesce("PremiumYTD", 0)) as "MonthlySpendYTD"
from
    "SummaryDataYTD"
where
    "CompanyId" = ?
    and "ImportDate" = ?
