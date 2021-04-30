select
  "Company"."CompanyName"
  , cc."CompanyId"
  , cc."ImportDate"
  , c."UserDescription" as "Carrier"
  , cc."CarrierId"
  , pt."UserDescription" as "PlanType"
  , cc."PlanTypeId"
  , p."UserDescription" as "Plan"
  , cc."PlanId"
  , l."FirstName"
  , l."LastName"
  , cc."CommissionablePremium"
  , cc."CommissionEffectiveDate"
  , TO_CHAR(cc."ImportDate" + interval '0 month', 'Month YYYY') as "DisplayDate"
  , TO_CHAR(cc."CommissionEffectiveDate" + interval '0 month', 'mm/dd/yyyy') as "DisplayCommissionEffectiveDate"
  , ccs."CommissionablePremiumTotal" as "Total"
  , ccs."CommissionablePremiumAgedMoreThanOneYear" as "Renewal"
  , ccs."CommissionablePremiumAgedOneYearOrLess" as "New"
  , cc."ResetRecord"
from
  "CompanyCommission" cc
  join "CompanyPlan" p on ( p."Id" = cc."PlanId")
  join "CompanyPlanType" pt on ( pt."Id" = cc."PlanTypeId" )
  join "CompanyCarrier" c on ( c."Id" = cc."CarrierId" )
  join "CompanyLife" l on ( l."Id" = cc."LifeId" )
  join "Company" on ( "Company"."Id" = cc."CompanyId")
  left join "CompanyCommissionSummary" ccs on ( ccs."CompanyId" = cc."CompanyId" and ccs."ImportDate" = cc."ImportDate" and ccs."LifeId" = cc."LifeId" and ccs."CarrierId" = cc."CarrierId" and ccs."PlanTypeId" = cc."PlanTypeId" and ccs."PlanId" = cc."PlanId" )
where
  cc."LifeId" = ?
  and cc."PlanId" = ?
order by
  cc."CompanyId" ASC
  , cc."ImportDate" DESC
  , cc."CommissionEffectiveDate" asc
  , cc."Id" ASC