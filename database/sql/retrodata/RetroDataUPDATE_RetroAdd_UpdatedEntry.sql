update "RetroData" set
	"AdjustmentType"=subquery."AdjustmentType"
	,"RetroDescription"=subquery."RetroDescription"
from (
	select
		"Id"
		, 2 as "AdjustmentType"
		, 'An updated entry with an effective date updated to a date prior to the current month.' as "RetroDescription"
	from
		"RetroData"
	where
		"CompanyId" = ?
		and "ImportDate" = ? -- current month
		and "Before-CoverageStartDate" <> "CoverageStartDate"
		and "CoverageStartDate" < ? -- current month		
		and "AdjustmentType" is null
) as subquery
where "RetroData"."Id" = subquery."Id"
