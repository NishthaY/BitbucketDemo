select
	case when count(*) = 0 then false else true end as is_bandable
from "PlanTypes" where "Name" = ? and "AgeBand" = true
