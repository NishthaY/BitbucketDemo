select
	sum(coalesce("AdjustedPremiumYTD", 0)) as "WashRetroSpendYTD"
from
    "SummaryDataYTD"
where
    "CompanyId" = ?
    and "ImportDate" = ?
