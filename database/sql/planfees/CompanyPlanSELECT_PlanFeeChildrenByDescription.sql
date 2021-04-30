select
    *
from
    "CompanyPlan"
where
    "CompanyId" = ?
    and "PlanNormalized" = ?
    and "ASOFee" is null
    and "StopLossFee" is null 
