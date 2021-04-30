select
	"Id" as "RetroDataLifeEventId"
    , "PlanId"
    , "Before-PlanList"
from
	"RetroDataLifeEvent"
where
	"CompanyId" = ?
	and "ImportDate" = ?
and (
	"PlanId"::text <> "Before-PlanList"
)
