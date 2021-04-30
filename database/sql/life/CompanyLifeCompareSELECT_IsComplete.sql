select
CASE when count(*) > 1 then false else true END as "IsComplete"
from "CompanyLifeCompare"
where
	"CompanyId" = ?
	and "ImportDate" = ?
	and "IsNewLife" is null
