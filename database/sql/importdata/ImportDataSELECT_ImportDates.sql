select
  "ImportDate"
  , "Finalized"
from
  "ImportData"
where
  "CompanyId" = ?
group by "ImportDate", "Finalized"
order by "ImportDate" asc