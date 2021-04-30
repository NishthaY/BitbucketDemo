select
	"AgeType"."Description" as "Age Type Description"
	, "AgeBand"."AgeTypeId" as "Age Type Id"
	, "AgeBand"."AnniversaryMonth" as "Anniversary Month"
	, "AgeBand"."AnniversaryDay" as "Anniversary Day"
from
	"AgeBand"
	join "AgeType" on ( "AgeBand"."AgeTypeId" = "AgeType"."Id" )
where
	"CompanyCoverageTierId" = ?
group by
	"AgeType"."Description"
	, "AgeBand"."AgeTypeId"
	, "AgeBand"."AnniversaryMonth"
	, "AgeBand"."AnniversaryDay"
