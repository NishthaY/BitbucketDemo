select
	"AgeType"."Id" as "AgeTypeId"
	, "AgeType"."Name" as "AgeTypeName"
	, "AgeBand"."AnniversaryMonth"
	, "AgeBand"."AnniversaryDay"
from
	"AgeBand"
	join "AgeType" on ( "AgeType"."Id" = "AgeBand"."AgeTypeId" )
where
	"AgeBand"."CompanyCoverageTierId" = ?
	and "AgeBand"."AgeTypeId" is not null limit 1
