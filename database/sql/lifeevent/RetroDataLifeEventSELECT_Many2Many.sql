select
	"RetroDataLifeEvent"."Id" as "RetroDataLifeEventId"
	, "RetroDataLifeEvent"."CoverageStartDate"
	, "RetroDataLifeEvent"."Before-CoverageStartDateList" as "BeforeCoverageStartDateList"
	, "CompanyCarrier"."UserDescription" as "Carrier"
	, "RetroDataLifeEvent"."Before-CoverageTierIdList" as "BeforeCoverageTierIdList"
	, "RetroData"."ImportDataId"
from
	"RetroDataLifeEvent"
	join "CompanyCarrier" on ( "CompanyCarrier"."Id" = "RetroDataLifeEvent"."CarrierId")
	join "RetroData" on ("RetroData"."Id" = "RetroDataLifeEvent"."RetroDataId" )
where
	"RetroDataLifeEvent"."CompanyId" = ?
	and "RetroDataLifeEvent"."ImportDate" = ?
	and "RetroDataLifeEvent"."Before-CoverageTierIdList" like '%,%'
