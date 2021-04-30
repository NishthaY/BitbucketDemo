update "LifeOriginalEffectiveDateCompare" set
  "CoverageStartDate" = subquery."CoverageStartDate"
  , "OriginalEffectiveDate" = subquery."OriginalEffectiveDate"
  , "Calculated-EffectiveDate" = subquery."Calculated-EffectiveDate"
  , "IsCoverageStartDate" = subquery."IsCoverageStartDate"
  , "Description" = subquery."Description"
  , "LostDate" = subquery."LostDate"
from(
  SELECT
    "LifeOriginalEffectiveDateCompare"."ImportDataId" as "ImportDataId"
    , "WashedData"."CoverageStartDate" AS "CoverageStartDate"
    , "ImportData"."OriginalEffectiveDate" AS "OriginalEffectiveDate"
    , CASE
      WHEN "ImportData"."OriginalEffectiveDate" IS NOT NULL
      THEN "ImportData"."OriginalEffectiveDate"
      ELSE "ImportData"."CoverageStartDate"
    END AS "Calculated-EffectiveDate"
    , CASE
        WHEN "ImportData"."OriginalEffectiveDate" IS NOT NULL
        THEN FALSE
        ELSE TRUE
      END AS "IsCoverageStartDate"
    , coalesce("LifeOriginalEffectiveDateCompare"."Description", '') || 'Previously lost coverage resumes.  Resetting the vault record with latest information. ' AS "Description"
    , "WashedData"."CoverageEndDate" AS "LostDate"
  FROM
    "LifeOriginalEffectiveDateCompare"
    JOIN "ImportData" ON ( "ImportData"."Id" = "LifeOriginalEffectiveDateCompare"."ImportDataId" )
    JOIN "WashedData" ON ( "WashedData"."ImportDataId" = "LifeOriginalEffectiveDateCompare"."ImportDataId" )
    JOIN "LifeOriginalEffectiveDate" ON
    (
      "LifeOriginalEffectiveDate"."LifeId" = "LifeOriginalEffectiveDateCompare"."LifeId"
      AND "LifeOriginalEffectiveDate"."CarrierId" = "LifeOriginalEffectiveDateCompare"."CarrierId"
      AND "LifeOriginalEffectiveDate"."PlanTypeId" = "LifeOriginalEffectiveDateCompare"."PlanTypeId"
      AND "LifeOriginalEffectiveDate"."PlanId" = "LifeOriginalEffectiveDateCompare"."PlanId"
      AND "LifeOriginalEffectiveDate"."CoverageTierId" = "LifeOriginalEffectiveDateCompare"."CoverageTierId"
    )
  WHERE
    "LifeOriginalEffectiveDateCompare"."CompanyId" = ?
    AND "LifeOriginalEffectiveDateCompare"."ImportDate" = ?
    AND "LifeOriginalEffectiveDateCompare"."Code" = 'RESTART'
) as subquery
where subquery."ImportDataId" = "LifeOriginalEffectiveDateCompare"."ImportDataId"
