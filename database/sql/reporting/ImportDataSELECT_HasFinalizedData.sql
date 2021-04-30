select
case when ( count(*) <> 0 ) then true else false end as "HasFinalizedData"
from "ImportData" where "CompanyId" = ? and "Finalized" = true
