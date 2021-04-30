update "CompanyCommission" as commission set "ResetRecord" = true from
  (
    select
      cc."CompanyId"
      , cc."ImportDate"
      , cc."LifeId"
      , cc."CarrierId"
      , cc."PlanTypeId"
      , cc."PlanId"
      , cc."CommissionEffectiveDate"
      , ROW_NUMBER() OVER (PARTITION BY (cc."CompanyId", cc."ImportDate", cc."LifeId", cc."CarrierId", cc."PlanTypeId", cc."PlanId", cc."CommissionEffectiveDate") ORDER BY cc."CommissionEffectiveDate" ASC) rn
    from
      "CompanyCommissionWorker" w
      join "CompanyCommission" cc on ( cc."CompanyId" = w."CompanyId" and cc."ImportDate" = w."ImportDate" and cc."LifeId" = w."LifeId" and cc."CarrierId" = w."CarrierId" and cc."PlanTypeId" = w."PlanTypeId" and cc."PlanId" = w."PlanId" and cc."CommissionEffectiveDate" = w."CommissionEffectiveDate" )
    where
      w."CompanyId" = ?
      and w."ImportDate" = ?
  ) as subquery
where
  commission."CompanyId" = subquery."CompanyId"
  and commission."ImportDate" = subquery."ImportDate"
  and commission."LifeId" = subquery."LifeId"
  and commission."CarrierId" = subquery."CarrierId"
  and commission."PlanTypeId" = subquery."PlanTypeId"
  and commission."PlanId" = subquery."PlanId"
  and commission."CommissionEffectiveDate" = subquery."CommissionEffectiveDate"
  and subquery.rn = 1