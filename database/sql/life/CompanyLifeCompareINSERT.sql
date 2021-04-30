insert into "CompanyLifeCompare" ( "CompanyId", "ImportDate", "LifeId", "LifeDataId", "IsNewLife", "UpdatesLifeId", "RollbackLifeKey", "RollbackFirstName", "RollbackLastName", "RollbackMiddleName", "RollbackEmployeeId", "RollbackSSN", "RollbackSSNDisplay", "RollbackDateOfBirth", "RollbackRelationship")
select
	"LifeData"."CompanyId"
	, "LifeData"."ImportDate"
	, "LifeData"."LifeId"
	, "LifeData"."Id"
	, null as "IsNewLife"
	, null as "UpdatesLifeId"
	, null as "RollbackLifeKey"
	, null as "RollbackFirstName"
	, null as "RollbackLastName"
	, null as "RollbackMiddleName"
	, null as "RollbackEmployeeId"
	, null as "RollbackSSN"
	, null as "RollbackSSNDisplay"
	, null as "RollbackDateOfBirth"
	, null as "RollbackRelationship"

from
	"LifeData"
	join "ImportData" on ( "ImportData"."Id" = "LifeData"."ImportDataId" )
where
	"LifeData"."CompanyId" = ?
	and "LifeData"."ImportDate" = ?
	and "LifeData"."NewLifeFlg" = true
	and "LifeData"."EIDExistedLastMonthFlg" = false
	and "ImportData"."EmployeeId" in (
		select "EmployeeId" from "CompanyLifeResearch" where "CurrentLifeKey" is null and "CompanyId" = "LifeData"."CompanyId" and "ImportDate" = "LifeData"."ImportDate" group by "EmployeeId"
	)
