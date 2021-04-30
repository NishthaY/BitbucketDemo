update "RetroData" set
	"AdjustmentType"=subquery."AdjustmentType"
	,"RetroDescription"=subquery."RetroDescription"
from (
	select
		"Id"
		, 6 as "AdjustmentType" -- new coverage tier
		, 'An updated record moving from one coverage tier to another with an effective date prior to the current month.' as "RetroDescription"
	from
		"RetroData"
	where
		"CompanyId" = ?
		and "ImportDate" = ? -- current month
		and "Before-CoverageTierKey" is not null
		and "CoverageTierKey" is not null
		and "Before-CoverageTierKey" <> "CoverageTierKey"
		and "CoverageStartDate" < ? -- current month
		and "AdjustmentType" is null
) as subquery
where "RetroData"."Id" = subquery."Id"
