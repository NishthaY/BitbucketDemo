select
	"CompanyLifeCompare"."LifeId"
	, "CompanyLife"."EmployeeId"
	, "ImportData"."FirstName"
	, "ImportData"."LastName"
	, "CompanyLife"."SSNDisplay"
	, "CompanyLife"."Relationship"
	, to_char("CompanyLife"."DateOfBirth", 'MM/DD/YYYY') as "DateOfBirth"
	, "CompanyLifeCompare"."IsNewLife"
	, "CompanyLifeCompare"."UpdatesLifeId"
from
	"CompanyLifeCompare"
	join "CompanyLife" on ( "CompanyLife"."CompanyId" = "CompanyLifeCompare"."CompanyId" and "CompanyLife"."Id" = "CompanyLifeCompare"."LifeId")
	join "LifeData" on ( "LifeData"."Id" = "CompanyLifeCompare"."LifeDataId" )
	join "ImportData" on ( "ImportData"."Id" = "LifeData"."ImportDataId" )
where
	"CompanyLifeCompare"."CompanyId" = ?
	and "CompanyLifeCompare"."ImportDate" = ?
	and "CompanyLifeCompare"."AutoSelected" = false
group by
	"CompanyLifeCompare"."LifeId"
	, "CompanyLife"."EmployeeId"
	, "ImportData"."FirstName"
	, "ImportData"."LastName"
	, "CompanyLife"."SSNDisplay"
	, "CompanyLife"."Relationship"
	, "CompanyLife"."DateOfBirth"
	, "CompanyLifeCompare"."IsNewLife"
	, "CompanyLifeCompare"."UpdatesLifeId"
order by  "ImportData"."LastName",  "ImportData"."FirstName", "CompanyLife"."SSNDisplay","CompanyLife"."Relationship","CompanyLife"."DateOfBirth"
