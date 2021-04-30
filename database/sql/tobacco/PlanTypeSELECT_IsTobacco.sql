select
	case when count(*) = 0 then false else true end as is_tobacco
from "PlanTypes" where "Name" = ? and "Tobacco" = true
