insert into "CompanyLifeResearch" ( "CompanyId", "ImportDate", "LifeDataId", "EmployeeId", "PreviousLifeKey" )
select
	"LifeData"."CompanyId"
	, "LifeData"."ImportDate" + '1month'::interval as "ImportDate"
	, "LifeData"."Id"
	, "ImportData"."EmployeeId"
	, "CompanyLife"."LifeKey" as "PreviousLifeKey"
from
	"LifeData"
	join "ImportData" on ( "ImportData"."Id" = "LifeData"."ImportDataId" )
	join "CompanyLife" on ( "CompanyLife"."CompanyId" = "LifeData"."CompanyId" and "CompanyLife"."Id" = "LifeData"."LifeId" and "CompanyLife"."Enabled" = true )
WHERE
	"LifeData"."CompanyId" = ?
	and "LifeData"."ImportDate" = to_date(?, 'MM/DD/YYYY') - '1month'::interval
	and "ImportData"."EmployeeId" in
	(

		select
			"ImportData"."EmployeeId"
		from
			"LifeData"
			join "ImportData" on ( "ImportData"."Id" = "LifeData"."ImportDataId" )
		WHERE
			"LifeData"."CompanyId" = ?
			and "LifeData"."ImportDate" = ?
			and "LifeData"."NewLifeFlg" = true
			and "LifeData"."EIDExistedLastMonthFlg" = false

	)
