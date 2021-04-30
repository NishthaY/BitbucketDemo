select
	"RetroDataLifeEvent"."CompanyId"
	, "RetroData"."ImportDataId"
	, "CompanyCarrier"."UserDescription" as "Carrier"
	, "RetroDataLifeEvent"."Before-CoverageTierIdList" as "BeforeCoverageTierIdList"
from
	"RetroDataLifeEvent"
	join "CompanyCarrier" on ( "CompanyCarrier"."Id" = "RetroDataLifeEvent"."CarrierId")
	join "RetroData" on ("RetroData"."Id" = "RetroDataLifeEvent"."RetroDataId" )
where
	"RetroDataLifeEvent"."CompanyId" = ?
	and "RetroDataLifeEvent"."ImportDate" = ?
	and "RetroDataLifeEvent"."Before-CoverageTierIdList" = ?
