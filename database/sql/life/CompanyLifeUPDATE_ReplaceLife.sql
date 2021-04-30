with t as (
	select
		"CompanyLife"."LifeKey"
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
		"CompanyLife"."Id" = ?  -- source_id
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
	"CompanyLife"."Id" = ? -- target_id
