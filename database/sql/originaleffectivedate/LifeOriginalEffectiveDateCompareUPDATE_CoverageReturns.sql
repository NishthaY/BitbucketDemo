update "LifeOriginalEffectiveDateCompare" set "Code" = subquery."Code", "Description" = subquery."Description" from
    (
        SELECT
            "WashedData"."ImportDataId"
             , "WashedData"."LifeId"
             , "WashedData"."CarrierId"
             , "WashedData"."PlanTypeId"
             , "WashedData"."PlanId"
             , "WashedData"."CoverageTierId"
             , "WashedData"."CoverageStartDate"
             , "WashedData"."CoverageEndDate"
             , 'UPDATE' as "Code"
             , coalesce("LifeOriginalEffectiveDateCompare"."Description", '') || 'Existing coverage was lost, but now returns. ' as "Description"
        FROM
            "LifeOriginalEffectiveDateCompare"
                JOIN "WashedData" ON ( "LifeOriginalEffectiveDateCompare"."ImportDataId" = "WashedData"."ImportDataId" )
                join "LifeOriginalEffectiveDate" on (
                        "LifeOriginalEffectiveDate"."LifeId" = "LifeOriginalEffectiveDateCompare"."LifeId"
                    and "LifeOriginalEffectiveDate"."CarrierId" = "LifeOriginalEffectiveDateCompare"."CarrierId"
                    and "LifeOriginalEffectiveDate"."PlanTypeId" = "LifeOriginalEffectiveDateCompare"."PlanTypeId"
                    and "LifeOriginalEffectiveDate"."PlanId" = "LifeOriginalEffectiveDateCompare"."PlanId"
                    and "LifeOriginalEffectiveDate"."CoverageTierId" = "LifeOriginalEffectiveDateCompare"."CoverageTierId"

                )
        WHERE
                "LifeOriginalEffectiveDateCompare"."CompanyId" = ?
          AND "LifeOriginalEffectiveDateCompare"."ImportDate" = ?
          AND "LifeOriginalEffectiveDate"."LostDate" is not null
          and "LifeOriginalEffectiveDateCompare"."Code" = 'EXISTING'
          AND coalesce("LifeOriginalEffectiveDate"."LostDate"::text, '') <> coalesce("LifeOriginalEffectiveDateCompare"."LostDate"::text, '')
    ) as subquery
where "LifeOriginalEffectiveDateCompare"."ImportDataId" = subquery."ImportDataId"