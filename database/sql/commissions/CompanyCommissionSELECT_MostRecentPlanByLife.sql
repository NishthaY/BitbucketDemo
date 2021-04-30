select
  "PlanId" as "RecentPlanId"
from
  "CompanyCommission" cc
where
  1=1
  and cc."CompanyId" = ?
  and cc."LifeId" = ?
order by "ImportDate" desc limit 1
