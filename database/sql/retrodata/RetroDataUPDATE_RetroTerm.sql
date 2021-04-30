update "RetroData" set
	"AdjustmentType"=subquery."AdjustmentType"
	,"RetroDescription"=subquery."RetroDescription"
from (
	select
		"Id"
		, 3 as "AdjustmentType"
		, 'An updated record showing a terminate date prior to the current month.' as "RetroDescription"
	from
		"RetroData"
	where
		"CompanyId" = ?
		and "ImportDate" = ? -- current month
		and (
			"Before-CoverageEndDate" is null and "CoverageEndDate" is not null
			or
			"Before-CoverageEndDate" <> "CoverageEndDate"
		)
		and "CoverageEndDate" < ? -- current month		
		and "AdjustmentType" is null
) as subquery
where "RetroData"."Id" = subquery."Id"
