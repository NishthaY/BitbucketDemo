select
		case when "CompanyLifeCompare"."IsNewLife" = true then 'Upload is New Life' else 'Upload Replaces Existing' end as "UserElection"
	, upload."FirstName" as "UploadLifeFirstName"
	, upload."MiddleName" as "UploadLifeMiddleName"
	, upload."LastName" as "UploadLifeLastName"
	, upload."SSNDisplay" as "UploadLifeSSNDisplay"
	, upload."DateOfBirth" as "UploadLifeDateOfBirth"
	, upload."Relationship" as "UploadLifeRelationship"
	, "CompanyLifeCompare"."RollbackFirstName" as "ExistingLifeFirstName"
	, "CompanyLifeCompare"."RollbackMiddleName" as "ExistingLifeMiddleName"
	, "CompanyLifeCompare"."RollbackLastName" as "ExistingLifeLastName"
	, "CompanyLifeCompare"."RollbackSSNDisplay" as "ExistingLifeSSNDisplay"
	, "CompanyLifeCompare"."RollbackDateOfBirth" as "ExistingLifeDateOfBirth"
	, "CompanyLifeCompare"."RollbackRelationship" as "ExistingLifeRelationship"
	, upload."EmployeeId"
	,"CompanyLifeCompare"."LifeId" as "UploadedLifeId"
	,"CompanyLifeCompare"."UpdatesLifeId" as "ExistingLifeId"
from
	"CompanyLifeCompare"
	join "LifeData" on ( "LifeData"."Id" = "CompanyLifeCompare"."LifeDataId" )
	join "CompanyLife" upload on ( upload."Id" = "CompanyLifeCompare"."LifeId" )
	left join "CompanyLife" existing on ( existing."Id" = "CompanyLifeCompare"."UpdatesLifeId" )
where
	"CompanyLifeCompare"."CompanyId" = ?
	and "CompanyLifeCompare"."ImportDate" = ?
