select
	trim(leading 'col' from "GroupCode")::int as "Column Number"
	, "Value" as "Column Mapping"
	, "GroupCode" as "Column Code"

from
	"CompanyParentPreference"
where
	"CompanyParentId" = ?
	and "Group" = 'column_map'
