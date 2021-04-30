select
CASE when count(*) >= 1 THEN true ELSE false END as exists
from "Wizard"
where "CompanyId" = ?
