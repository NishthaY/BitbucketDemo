select
    DISTINCT(pt."Display") as "UserDescription"
from
    "CompanyPlan" cp
        join "CompanyPlanType" cpt on ( cp."PlanTypeId" = cpt."Id")
        join "PlanTypes" pt on ( pt."Name" = cpt."PlanTypeCode")
where
        cp."CompanyId" = ?
  and cp."PlanNormalized" = ?