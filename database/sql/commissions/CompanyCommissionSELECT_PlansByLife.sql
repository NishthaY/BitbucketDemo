select
  "PlanId"
  ,p."UserDescription"
from
  "CompanyCommission" cc
  join "CompanyPlan" p on ( p."Id" = cc."PlanId" )
where
  1=1
  and cc."CompanyId" = ?
  and cc."LifeId" = ?
group by cc."PlanId", p."UserDescription"
order by "PlanId" desc