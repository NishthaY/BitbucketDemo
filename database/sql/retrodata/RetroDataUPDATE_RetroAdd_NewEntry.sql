update "RetroData" set
	"AdjustmentType"=subquery."AdjustmentType"
	,"RetroDescription"=subquery."RetroDescription"
from (
	select
		"Id"
		, 2 as "AdjustmentType"
		, 'A new entry not seen previously but with an effective date prior to the current month.' as "RetroDescription"
	from
		"RetroData"
	where
		"CompanyId" = ?
		and "ImportDate" = ? -- current month
		and "Before-CoverageStartDate" is null
		and "CoverageStartDate" < ? -- current month
		and "AdjustmentType" is null
) as subquery
where "RetroData"."Id" = subquery."Id"
