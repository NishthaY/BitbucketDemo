select
	"CompanyLifeCompare"."LifeId" as "LifeId"
	, "CompanyLife"."SSN" as "SSN"
	, "CompanyLife"."FirstName"
	, "CompanyLife"."LastName"
	, "CompanyLife"."MiddleName"
	, "CompanyLife"."EmployeeId"
	, "CompanyLife"."DateOfBirth"
	, "CompanyLife"."Relationship"
from
	"CompanyLifeCompare"
	join "CompanyLife" on ( "CompanyLife"."CompanyId" = "CompanyLifeCompare"."CompanyId" and "CompanyLife"."Id" = "CompanyLifeCompare"."LifeId" )
where
	"CompanyLifeCompare"."CompanyId" = ?
	and "CompanyLifeCompare"."ImportDate" = ?
	and "IsNewLife" is null
	and "UpdatesLifeId" is null
	and "CompanyLifeCompare"."Id" is not null
group by
	"CompanyLifeCompare"."LifeId"
	, "CompanyLife"."SSN"
	, "CompanyLife"."FirstName"
	, "CompanyLife"."LastName"
	, "CompanyLife"."MiddleName"
	, "CompanyLife"."EmployeeId"
	, "CompanyLife"."DateOfBirth"
	, "CompanyLife"."Relationship"
