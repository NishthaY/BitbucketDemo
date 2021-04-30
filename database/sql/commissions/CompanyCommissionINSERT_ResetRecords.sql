-- Create new CompanyCommission records for the company life-plan records
-- for this import that are maked as RESET.
insert into "CompanyCommission" ( "CompanyId", "ImportDate", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "CommissionEffectiveDate", "CommissionablePremium", "ResetRecord" )
select
  compare."CompanyId"
  , compare."ImportDate"
  , compare."LifeId"
  , compare."CarrierId"
  , compare."PlanTypeId"
  , compare."PlanId"
  , data."Calculated-EffectiveDate" as "CommissionEffectiveDate"
  , data."MonthlyCost" as "CommissionablePremium"
  , true as "ResetRecord"
from
  "CompanyCommissionDataCompare" compare
  left join "CompanyCommissionData" data on ( data."CompanyId" = compare."CompanyId" and data."ImportDate" = compare."ImportDate" and data."LifeId" = compare."LifeId" and data."CarrierId" = compare."CarrierId" and data."PlanTypeId" = compare."PlanTypeId" and data."PlanId" = compare."PlanId" and data."CoverageTierId" = compare."CoverageTierId" )
where
  compare."CompanyId" = ?
  and compare."ImportDate" = ?
  and compare."Code" = 'RESET'