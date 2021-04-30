with t as (
	select
		"CompanyLife"."LifeKey"
	from
		"LifeData"
		join "ImportData" on ( "ImportData"."Id" = "LifeData"."ImportDataId" )
		join "CompanyLife" on ( "CompanyLife"."CompanyId" = "LifeData"."CompanyId" and "CompanyLife"."Id" = "LifeData"."LifeId" and "CompanyLife"."Enabled" = true)
	WHERE
		"LifeData"."CompanyId" = ?
		and "LifeData"."ImportDate" = to_date(?, 'MM/DD/YYYY')
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
		)
)
update "CompanyLifeResearch"
set "CurrentLifeKey" = t."LifeKey"
from t
where "PreviousLifeKey" = t."LifeKey"
