-- Using the data stored in the worker table, which is a collection of
-- life/plan records with the billing monthly cost, update the validate
-- table so we can see the commissionable premium and the billing monthly
-- cost next to each other.
update "CompanyCommissionValidate" v
set "MonthlyCost" = w."MonthlyCost"
from
  "CompanyCommissionWorker" w
where
  v."CompanyId" = ?
  and v."ImportDate" = ?
  and v."CompanyId" = w."CompanyId"
  and v."ImportDate" = w."ImportDate"
  and v."LifeId" = w."LifeId"
  and v."CarrierId" = w."CarrierId"
  and v."PlanTypeId" = w."PlanTypeId"
  and v."PlanId" = w."PlanId"