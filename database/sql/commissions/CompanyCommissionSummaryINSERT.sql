insert into "CompanyCommissionSummary" ( "CompanyId", "ImportDate", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "CommissionablePremiumTotal", "CommissionablePremiumAgedMoreThanOneYear", "CommissionablePremiumAgedOneYearOrLess" )
select
  "CompanyId"
  , "ImportDate"
  , "LifeId"
  , "CarrierId"
  , "PlanTypeId"
  , "PlanId"
  , coalesce(sum("CommissionablePremium"), 0) as "TotalCommissionablePremium"
  , coalesce(sum("CommissionablePremium") filter ( where extract(YEAR from Age("ImportDate", "CommissionEffectiveDate")) >= 1 ), 0) as "CommissionablePremiumOlderThanOneYear"
  , coalesce(sum("CommissionablePremium") filter ( where extract(YEAR from Age("ImportDate", "CommissionEffectiveDate")) = 0 ), 0) as "CommissionablePremiumLessThanOneYear"
from
  "CompanyCommission"
where
  "CompanyId" = ?
  and "ImportDate" = ?
group by
  "CompanyId", "ImportDate", "LifeId", "CarrierId", "PlanTypeId", "PlanId"