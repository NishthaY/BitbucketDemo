update "CompanyLifeCompare"
set
	"IsNewLife" = true
	, "UpdatesLifeId" = null
	, "RollbackLifeKey" = null
	, "RollbackFirstName" = null
	, "RollbackMiddleName" = null
	, "RollbackEmployeeId" = null
	, "RollbackSSN" = null
	, "RollbackSSNDisplay" = null
	, "RollbackDateOfBirth" = null
	, "RollbackRelationship" = null
where
	"CompanyId" = ?
	and "ImportDate" = ?
	and "LifeId" = ?
