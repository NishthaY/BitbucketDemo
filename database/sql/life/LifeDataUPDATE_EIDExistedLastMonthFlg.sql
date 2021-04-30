with t as (
    select
    	ld_now."Id" as id
    	, CASE when ( ld_before."Id" is null ) then false else true END as calculated
    from
    	"LifeData" as ld_now
    	join "ImportData" on ( ld_now."ImportDataId" = "ImportData"."Id" )
    	left join "LifeData" as ld_before on
    	(
				ld_before."CompanyId" = ld_now."CompanyId"
				and ld_before."ImportDate" = ld_now."ImportDate" - '1month'::interval
    		and ld_before."LifeId" = ld_now."LifeId"
    	)
    where
    	ld_now."CompanyId" = ?
    	and ld_now."ImportDate" = ?
			and ld_now."EIDExistedLastMonthFlg" is NULL
)
update "LifeData"
set "EIDExistedLastMonthFlg" = t.calculated
from t
where "Id" = id
