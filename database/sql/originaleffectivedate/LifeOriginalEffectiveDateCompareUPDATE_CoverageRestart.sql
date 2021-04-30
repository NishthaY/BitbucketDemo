update "LifeOriginalEffectiveDateCompare" set
  "Code" = 'RESTART'
from
  (
    select
      "LifeOriginalEffectiveDateCompare"."ImportDataId"
    from
      "LifeOriginalEffectiveDateCompare"
      join "LifeOriginalEffectiveDate" on
                                         (
                                           "LifeOriginalEffectiveDate"."LifeId" = "LifeOriginalEffectiveDateCompare"."LifeId"
                                           and "LifeOriginalEffectiveDate"."CarrierId" = "LifeOriginalEffectiveDateCompare"."CarrierId"
                                           and "LifeOriginalEffectiveDate"."PlanTypeId" = "LifeOriginalEffectiveDateCompare"."PlanTypeId"
                                           and "LifeOriginalEffectiveDate"."PlanId" = "LifeOriginalEffectiveDateCompare"."PlanId"
                                           and "LifeOriginalEffectiveDate"."CoverageTierId" = "LifeOriginalEffectiveDateCompare"."CoverageTierId"
                                           )
    WHERE
      "LifeOriginalEffectiveDateCompare"."CompanyId" = ?
      AND "LifeOriginalEffectiveDateCompare"."ImportDate" = ?
      and "LifeOriginalEffectiveDateCompare"."Code" = 'EXISTING'
      and "LifeOriginalEffectiveDateCompare"."CoverageStartDate" > "LifeOriginalEffectiveDate"."LostDate"
  ) as subquery
where
  subquery."ImportDataId" = "LifeOriginalEffectiveDateCompare"."ImportDataId"
