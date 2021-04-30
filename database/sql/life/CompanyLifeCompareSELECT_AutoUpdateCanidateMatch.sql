select
		"CompanyLife"."Id" as "UpdatesLifeId"
	, "CompanyLife"."SSN" as "UpdatesLifeIdSSN"
from
	"CompanyLifeResearch"
	join "LifeData" on ( "CompanyLifeResearch"."LifeDataId" = "LifeData"."Id" )
	join "CompanyLife" on ( "CompanyLife"."CompanyId" = "CompanyLifeResearch"."CompanyId" and "CompanyLife"."Id" = "LifeData"."LifeId" )

where
	"CompanyLifeResearch"."CurrentLifeKey" is null
	and "CompanyLifeResearch"."CompanyId" = ?
	and "CompanyLifeResearch"."ImportDate" = ?
	and "CompanyLife"."SSN" = ?
group by
	"CompanyLife"."Id", "CompanyLife"."SSN"
