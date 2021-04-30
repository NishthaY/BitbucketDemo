select
    DISTINCT("PlanAnniversaryMonth") as "PlanAnniversaryMonth"
from
    "CompanyPlanType" where "CompanyId" = ?
    and "PlanAnniversaryMonth" <> 0
    and "Ignored" = false