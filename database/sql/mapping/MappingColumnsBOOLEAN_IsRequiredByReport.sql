select
  case when count(*) = 0 then false else true end as "Required"
from
  "ReportProperties"
where
  "ReportCode" = ? and "Group" = 'REQUIRED_COLUMN' and "Key" = ? and "Value" = 'TRUE'