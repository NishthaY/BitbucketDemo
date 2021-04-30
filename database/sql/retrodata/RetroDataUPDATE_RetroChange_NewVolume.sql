update "RetroData" set
	"AdjustmentType"=subquery."AdjustmentType"
	,"RetroDescription"=subquery."RetroDescription"
from (
	select
		"Id"
		, 5 as "AdjustmentType" -- "change-employer-cost"
		, 'An updated record where volume changed from one month to another with an effective date prior to the current month.' as "RetroDescription"
	from
		"RetroData"
	where
		"CompanyId" = ?
		and "ImportDate" = ? -- current month
		and "Before-Volume" <> "Volume"::text
		and "CoverageStartDate" < ? -- current month
and "AdjustmentType" is null
		and "AdjustmentType" is null
) as subquery
where "RetroData"."Id" = subquery."Id"
