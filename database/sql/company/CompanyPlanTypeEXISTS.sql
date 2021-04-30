select
    CASE when ( count(*) >= 1 ) then true else false END as exists
from
    "CompanyPlanType"
where
    "CompanyId" = ?
    and upper("Carrier") = upper(?)
    and upper("PlanType") = upper(?)
