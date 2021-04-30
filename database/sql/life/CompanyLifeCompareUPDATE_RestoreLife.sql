with t as (
	select
		"RollbackLifeKey" as "LifeKey"
		, "RollbackFirstName" as "FirstName"
		, "RollbackLastName" as "LastName"
		, "RollbackMiddleName" as "MiddleName"
		, "RollbackEmployeeId" as "EmployeeId"
		, "RollbackSSN" as "SSN"
		, "RollbackSSNDisplay" as "SSNDisplay"
		, "RollbackDateOfBirth" as "DateOfBirth"
		, "RollbackRelationship" as "Relationship"
	from
		"CompanyLifeCompare"
	WHERE
		"CompanyLifeCompare"."CompanyId" = ?
		and "CompanyLifeCompare"."ImportDate" = ?
		and "CompanyLifeCompare"."LifeId" = ?
	group by
		"RollbackLifeKey"
		, "RollbackFirstName"
		, "RollbackLastName"
		, "RollbackMiddleName"
		, "RollbackEmployeeId"
		, "RollbackSSN"
		, "RollbackSSNDisplay"
		, "RollbackDateOfBirth"
		, "RollbackRelationship"
)
update "CompanyLife"
set
	"LifeKey" = t."LifeKey"
	, "FirstName" = t."FirstName"
	, "LastName" = t."LastName"
	, "MiddleName" = t."MiddleName"
	, "EmployeeId" = t."EmployeeId"
	, "SSN" = t."SSN"
	, "SSNDisplay" = t."SSNDisplay"
	, "DateOfBirth" = t."DateOfBirth"
	, "Relationship" = t."Relationship"
from t
where
	"CompanyLife"."Id" = ?
