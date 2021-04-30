select
  ((?::date) + ("RetroRule"::int * '-1 month'::INTERVAL))::date as "RetroWindowStart"
from
  "CompanyPlanType"
where
  "CompanyId" = ?
  and "CarrierId" = ?
  and "Id" = ?