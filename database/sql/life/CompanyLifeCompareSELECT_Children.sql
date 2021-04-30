select
	"CompanyLife"."Id"
	, "CompanyLife"."EmployeeId"
	, "ImportData"."FirstName"
	, "ImportData"."LastName"
	, "CompanyLife"."SSNDisplay"
	, "CompanyLife"."Relationship"
	, to_char("CompanyLife"."DateOfBirth", 'MM/DD/YYYY') as "DateOfBirth"
from
	"CompanyLifeResearch"
	join "LifeData" on ( "LifeData"."Id" = "CompanyLifeResearch"."LifeDataId" )
	join "CompanyLife" on ( "CompanyLife"."CompanyId" = "CompanyLifeResearch"."CompanyId" and "CompanyLife"."Id" = "LifeData"."LifeId")
	join "ImportData" on ( "ImportData"."Id" = "LifeData"."ImportDataId" )
where
	"CompanyLifeResearch"."CompanyId" = ?
	and "CompanyLifeResearch"."ImportDate" = ?
	and "CompanyLifeResearch"."EmployeeId" = ?
	and "CompanyLifeResearch"."CurrentLifeKey" is null
group by
	"CompanyLife"."Id"
	, "CompanyLife"."EmployeeId"
	, "ImportData"."FirstName"
	, "ImportData"."LastName"
	, "CompanyLife"."SSNDisplay"
	, "CompanyLife"."Relationship"
	, "CompanyLife"."DateOfBirth"
order by
	"ImportData"."LastName"
	,"ImportData"."FirstName"
	,"CompanyLife"."Relationship"
	,"CompanyLife"."DateOfBirth"
	,"CompanyLife"."SSNDisplay"
