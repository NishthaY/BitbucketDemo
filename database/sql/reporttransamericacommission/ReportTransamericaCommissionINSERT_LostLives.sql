insert into "ReportTransamericaCommission" ( "CompanyId", "ImportDate", "ImportDataId", "MasterPolicy", "EmployeeNumber", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId", "ProductType", "Option", "Tier", "LostLife")
select
    last."CompanyId"
     , last."ImportDate" + INTERVAL '+1 month' as "ImportDate"
     , last."ImportDataId"
     , last."MasterPolicy"
     , last."EmployeeNumber"
     , last."LifeId"
     , last."CarrierId"
     , last."PlanTypeId"
     , last."PlanId"
     , last."CoverageTierId"
     , last."ProductType" as "ProductType"
     , last."Option" as "Option"
     , last."Tier"
     , true as "LostLife"
from
    "ReportTransamericaCommission" last
        left join "ReportTransamericaCommission" active on (
            active."CompanyId" = last."CompanyId"
            and active."ImportDate" = ?     -- current month
            and last."LifeId" = active."LifeId"
            and last."CarrierId" = active."CarrierId"
            and last."PlanTypeId" = active."PlanTypeId"
            and last."PlanId" = active."PlanId"
            and last."CoverageTierId" = active."CoverageTierId"  )
where
    last."CompanyId" = ?
    and last."ImportDate" = ?       -- previous month
    and last."LostLife" = false
    and active."Id" is null