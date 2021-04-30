with t as (
	select
		"CompanyLife"."Id"
		, "CompanyLife"."LifeKey"
		, "CompanyLife"."FirstName"
		, "CompanyLife"."LastName"
		, "CompanyLife"."MiddleName"
		, "CompanyLife"."EmployeeId"
		, "CompanyLife"."SSN"
		, "CompanyLife"."SSNDisplay"
		, "CompanyLife"."DateOfBirth"
		, "CompanyLife"."Relationship"
	from
		"CompanyLife"
	WHERE
		"CompanyLife"."Id" = ?
)
update "CompanyLifeCompare"
set
	"IsNewLife" = false
	, "UpdatesLifeId" = t."Id"
	, "RollbackLifeKey" = t."LifeKey"
	, "RollbackFirstName" = t."FirstName"
	, "RollbackLastName" = t."LastName"
	, "RollbackMiddleName" = t."MiddleName"
	, "RollbackEmployeeId" = t."EmployeeId"
	, "RollbackSSN" = t."SSN"
	, "RollbackSSNDisplay" = t."SSNDisplay"
	, "RollbackDateOfBirth" = t."DateOfBirth"
	, "RollbackRelationship" = t."Relationship"
from t
where
	"CompanyLifeCompare"."CompanyId" = ?
	and "CompanyLifeCompare"."ImportDate" = ?
	and "CompanyLifeCompare"."LifeId" = ?
