select
    DISTINCT(p."UserDescription") as "UserDescription"
from
    "CompanyCoverageTier" t
        join "CompanyPlan" p on ( t."PlanId" = p."Id")
where
        t."CompanyId" = ?
  and t."CoverageTierNormalized" = ?