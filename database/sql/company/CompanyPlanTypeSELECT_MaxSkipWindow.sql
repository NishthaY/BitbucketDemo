select
    min("RetroRule") as "MaxSkipWindow"
from
    "CompanyPlanType"
where "CompanyId" = ?
    and "Ignored" = false