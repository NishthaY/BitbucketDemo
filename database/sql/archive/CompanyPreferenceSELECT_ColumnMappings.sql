select
	trim(leading 'col' from "GroupCode")::int as "Column Number"
	, "Value" as "Column Mapping"
	, "GroupCode" as "Column Code"

from
	"CompanyPreference"
where
	"CompanyId" = ?
	and "Group" = 'column_map'
