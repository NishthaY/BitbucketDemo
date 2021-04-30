insert into "CompanyCommissionWarning" ( "CompanyId", "ImportDate", "ImportDataId", "Tag", "Issue" )
select
  c."CompanyId"
  , c."ImportDate"
  , o."ImportDataId"
  , 'CommissionProcessing' as "Tag"
  , c."Description"
from
  "CompanyCommissionDataCompare" c
  left join "LifeOriginalEffectiveDateCompare" o on
                                                   (
                                                     c."CompanyId" = o."CompanyId"
                                                     and c."ImportDate" = o."ImportDate"
                                                     and c."LifeId" = o."LifeId"
                                                     and c."CarrierId" = o."CarrierId"
                                                     and c."PlanTypeId" = o."PlanTypeId"
                                                     and c."PlanId" = o."PlanId"
                                                     and c."CoverageTierId" = o."CoverageTierId"
                                                     )
where
  c."CompanyId" = ?
  and c."ImportDate" = ?
  and c."Code" = 'WARNING'
group by c."CompanyId" , c."ImportDate" , o."ImportDataId" , c."Description"