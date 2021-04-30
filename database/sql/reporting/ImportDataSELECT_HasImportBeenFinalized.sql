select
case when ( count(*) <> 0 ) then true else false end as "HasBeenFinalized"
from "ImportData" where "CompanyId" = ? and "ImportDate" = ? and "Finalized" = true
