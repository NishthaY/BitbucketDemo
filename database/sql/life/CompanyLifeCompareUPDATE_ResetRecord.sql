update "CompanyLifeCompare"
set
	"IsNewLife" = null
	, "UpdatesLifeId" = null
	, "RollbackLifeKey" = null
	, "RollbackFirstName" = null
	, "RollbackMiddleName" = null
    , "RollbackLastName" = null
	, "RollbackEmployeeId" = null
	, "RollbackSSN" = null
	, "RollbackSSNDisplay" = null
	, "RollbackDateOfBirth" = null
	, "RollbackRelationship" = null
where
	"CompanyId" = ?
	and "ImportDate" = ?
	and "LifeId" = ?
