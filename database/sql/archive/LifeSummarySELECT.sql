select
  count(*) as total
from
  "CompanyLife"
where
  "CompanyId" = ?
  and "Enabled" = true