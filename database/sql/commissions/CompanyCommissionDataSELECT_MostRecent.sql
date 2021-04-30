select
    d."ImportDate" as "LastImportDate"
     , extract(month from age( d."ImportDate",'{IMPORT_DATE}'::date)) as "CoverageGapOffset"
from
    "CompanyCommissionData" d
    left join "CompanyCommissionDataCompare" c on (c."CompanyId" = d."CompanyId" and c."ImportDate" = d."ImportDate" and  c."LifeId" = d."LifeId" and c."CarrierId" = d."CarrierId" and c."PlanTypeId" = d."PlanTypeId" and c."PlanId" = d."PlanId")  --  and before."CoverageTierId" = now."CoverageTierId"
where
  d."CompanyId" = ?
  and d."LifeId" = ?
  and d."CarrierId" = ?
  and d."PlanTypeId" = ?
  and d."PlanId" = ?
  and d."ImportDate" <> ?
  and coalesce(c."Code", '') <> 'IGNORE'  -- Skip records that are terminated (ignored)
order by d."ImportDate" desc
limit 1