insert into "CompanyLife" ( "CompanyId", "LifeKey", "FirstName", "LastName", "MiddleName", "EmployeeId", "SSN", "SSNDisplay", "DateOfBirth", "Relationship" )
select
	"ImportData"."CompanyId"
	, "ImportLife"."LifeKey"
	, "ImportData"."FirstName"
	, "ImportData"."LastName"
	, "ImportData"."MiddleName"
	, "ImportData"."EmployeeId"
	, "ImportData"."SSN"
	, "ImportData"."SSNDisplay"
	, "ImportData"."DateOfBirth"
	, "ImportData"."Relationship"
FROM
	"ImportData"
	join "ImportLife" on ( "ImportLife"."ImportDataId" = "ImportData"."Id" )
	left join "CompanyLife" on ( "CompanyLife"."CompanyId" = "ImportData"."CompanyId" and "CompanyLife"."LifeKey" = "ImportLife"."LifeKey" )
WHERE
	"ImportData"."CompanyId" = ?
	and "ImportData"."ImportDate" = ?
	and "CompanyLife"."LifeKey" is null
group by
	"ImportData"."CompanyId"
	, "ImportLife"."LifeKey"
	, "ImportData"."FirstName"
	, "ImportData"."LastName"
	, "ImportData"."MiddleName"
	, "ImportData"."EmployeeId"
	, "ImportData"."SSN"
	, "ImportData"."SSNDisplay"
	, "ImportData"."DateOfBirth"
	, "ImportData"."Relationship"