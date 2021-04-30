update "ProcessQueue" set
	"StartTime" = null
	, "EndTime" = null
	, "Failed" = null
	, "ErrorMessage" = null
	, "ProcessId" = null
where
	"Id" = ?
