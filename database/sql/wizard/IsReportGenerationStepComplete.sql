select
CASE when count(*) >= 1 THEN true ELSE false END as complete
from "Wizard"
where "CompanyId" = ?
and "ReportGenerationComplete" = true
