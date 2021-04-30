select
	"CompanyLife"."Id" as "UpdatesLifeId"
from
	"CompanyLifeResearch"
	join "LifeData" on ( "CompanyLifeResearch"."LifeDataId" = "LifeData"."Id" )
	join "CompanyLife" on ( "CompanyLife"."CompanyId" = "CompanyLifeResearch"."CompanyId" and "CompanyLife"."Id" = "LifeData"."LifeId" )

where
	"CompanyLifeResearch"."CurrentLifeKey" is null
	and "CompanyLifeResearch"."CompanyId" = ?
	and "CompanyLifeResearch"."ImportDate" = ?
	and upper(coalesce("CompanyLife"."SSN", '')) <> UPPER(coalesce(?, ''))
	and upper(coalesce("CompanyLife"."FirstName", '')) = UPPER(coalesce(?, ''))
	and upper(coalesce("CompanyLife"."LastName", '')) = UPPER(coalesce(?, ''))
	and upper(coalesce("CompanyLife"."MiddleName", '')) = UPPER(coalesce(?, ''))
	and upper(coalesce("CompanyLife"."EmployeeId", '')) = UPPER(coalesce(?, ''))
	and "CompanyLife"."DateOfBirth" = ?
	and upper(coalesce("CompanyLife"."Relationship", '')) = UPPER(coalesce(?, ''))
group by "CompanyLife"."Id"
