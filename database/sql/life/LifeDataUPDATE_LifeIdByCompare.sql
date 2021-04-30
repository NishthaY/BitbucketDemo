with t as (
	select
		 "CompanyLifeCompare"."LifeDataId"
	from
		"CompanyLifeCompare"
	where
		"CompanyLifeCompare"."CompanyId" = ?
		and "CompanyLifeCompare"."ImportDate" = ?
		and "CompanyLifeCompare"."LifeId" = ?
)
update "LifeData"
set "LifeId" = ?
from t
where
	"LifeData"."Id" = t."LifeDataId"
