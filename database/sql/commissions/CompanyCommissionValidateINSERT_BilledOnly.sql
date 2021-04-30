-- Find records that were billed this month but did not have a cooresponding
-- commission component.  Insert those into the validation table so we can
-- track them.
insert into "CompanyCommissionValidate" ( "CompanyId", "ImportDate", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "MonthlyCost" )
select
  w."CompanyId"
  , w."ImportDate"
  , w."LifeId"
  , w."CarrierId"
  , w."PlanTypeId"
  , w."PlanId"
  , w."MonthlyCost"
from
  "CompanyCommissionValidate" v
  left join "CompanyCommissionWorker" w on ( v."CompanyId" = w."CompanyId" and v."ImportDate" = w."ImportDate" and v."LifeId" = w."LifeId" and v."CarrierId" = w."CarrierId" and v."PlanTypeId" = w."PlanTypeId" and v."PlanId" = w."PlanId" )
where
  v."CompanyId" = ?
  and v."ImportDate" = ?
  and w."Id" is null