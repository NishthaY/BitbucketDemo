select
	"CompanyId"
	, "Id" as "PlanId"
	, "PlanTypeId"
	, "PlanNormalized"
from
	"CompanyPlan"
where
	"CompanyId" = ?
	and ( "ASOFee" is not null OR "StopLossFee" is not null )
group by
	"CompanyId"
	, "PlanId"
	, "PlanTypeId"
	, "PlanNormalized" 
