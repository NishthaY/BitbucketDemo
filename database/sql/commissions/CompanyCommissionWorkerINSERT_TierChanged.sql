insert into "CompanyCommissionWorker" ( "CompanyId", "ImportDate", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId" )
select
  "CompanyId", "ImportDate", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId"
from "CompanyCommissionData"
where "CompanyId" = ? and "ImportDate" = ?
      and "OEDCode" = 'NEW'
