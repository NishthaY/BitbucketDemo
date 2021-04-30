insert into "CompanyCommissionWorker" ( "CompanyId", "ImportDate", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId" )
select
    now."CompanyId"
     ,now."ImportDate"
     ,now."LifeId"
     ,now."CarrierId"
     ,now."PlanTypeId"
     ,now."PlanId"
     ,now."CoverageTierId"
from
    "CompanyCommissionDataCompare" now
    left join "CompanyCommissionDataCompare" before on (before."CompanyId" = now."CompanyId" and before."ImportDate" = now."ImportDate" - interval '1 month' and  before."LifeId" = now."LifeId" and before."CarrierId" = now."CarrierId" and before."PlanTypeId" = now."PlanTypeId" and before."PlanId" = now."PlanId")  --  and before."CoverageTierId" = now."CoverageTierId"
where
    now."CompanyId" = ?
    and now."ImportDate" = ?
    and before."Code" = 'IGNORE'
    and ( now."Code" is null OR now."Code" <> 'IGNORE')