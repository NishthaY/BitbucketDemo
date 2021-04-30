insert into "LifeData" ( "CompanyId", "ImportDataId", "ImportDate", "LifeId", "NewLifeFlg", "EIDExistedLastMonthFlg" )
select
	"ImportData"."CompanyId"
	, "ImportData"."Id" as "ImportDataId"
	, "ImportData"."ImportDate"
	, "CompanyLife"."Id" as "LifeId"
	, null as "NewLifeFlg"
	, null as "EIDExistedLastMonthFlg"
from
	"ImportData"
	join "ImportLife" on ( "ImportLife"."ImportDataId" = "ImportData"."Id" )
	join "CompanyLife" on ( "CompanyLife"."CompanyId" = "ImportData"."CompanyId" and "CompanyLife"."LifeKey" = "ImportLife"."LifeKey" )
	left join "LifeData" on
	(
		"LifeData"."CompanyId" = "ImportData"."CompanyId"
		and "LifeData"."ImportDate" = "ImportData"."ImportDate"
		and "LifeData"."ImportDataId" = "ImportData"."Id"
	)
where
	"ImportData"."CompanyId" = ?
	and "ImportData"."ImportDate" = ?
	and "CompanyLife"."Enabled" = true
	and "LifeData"."Id" is null