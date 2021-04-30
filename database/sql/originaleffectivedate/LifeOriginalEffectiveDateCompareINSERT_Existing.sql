insert into "LifeOriginalEffectiveDateCompare" ( "ImportDataId", "CompanyId", "ImportDate", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId", "CoverageStartDate", "OriginalEffectiveDate", "Calculated-EffectiveDate", "IsCoverageStartDate", "LostDate", "Code" )
select
  "ImportData"."Id" as "ImportDataId"
  , "ImportData"."CompanyId"
  , "ImportData"."ImportDate"
  , "LifeData"."LifeId"
  , "WashedData"."CarrierId" as "CarrierId"
  , "WashedData"."PlanTypeId" as "PlanTypeId"
  , "WashedData"."PlanId" as "PlanId"
  , "WashedData"."CoverageTierId" as "CoverageTierId"
  , "ImportData"."CoverageStartDate"
  , "ImportData"."OriginalEffectiveDate"
  --, CASE WHEN "ImportData"."OriginalEffectiveDate" is not null THEN "ImportData"."OriginalEffectiveDate" ELSE "ImportData"."CoverageStartDate" END as "CalculatedEffectiveDate"
  --, CASE WHEN "ImportData"."OriginalEffectiveDate" is not null THEN false ELSE true END as "IsCoverageStartDate"

  -- Since we are setting the EXISTING flag, the implication is that this cannot be the first
  -- import.  This means we should always show the CSD as the OED and set the IsCSD flag to true.
  , "ImportData"."CoverageStartDate" as "CalculatedEffectiveDate"
  , true as "IsCoverageStartDate"

  , "WashedData"."CoverageEndDate"
  , 'EXISTING' as "Code"
from
  "ImportData"
  join "LifeData" on (
    "LifeData"."CompanyId" = "ImportData"."CompanyId"
    and "LifeData"."ImportDate" = "ImportData"."ImportDate"
    and "LifeData"."ImportDataId" = "ImportData"."Id"
  )
  join "WashedData" on ( "WashedData"."ImportDataId" = "ImportData"."Id" )
  left join "LifeOriginalEffectiveDate" on (
    "LifeOriginalEffectiveDate"."LifeId" = "LifeData"."LifeId"
    and "LifeOriginalEffectiveDate"."CarrierId" = "WashedData"."CarrierId"
    and "LifeOriginalEffectiveDate"."PlanTypeId" = "WashedData"."PlanTypeId"
    and "LifeOriginalEffectiveDate"."PlanId" = "WashedData"."PlanId"
    and "LifeOriginalEffectiveDate"."CoverageTierId" = "WashedData"."CoverageTierId"
  )
  left join "LifeOriginalEffectiveDateCompare" on (
    "LifeOriginalEffectiveDateCompare"."CompanyId" = "ImportData"."CompanyId"
    and "LifeOriginalEffectiveDateCompare"."ImportDate" = "ImportData"."ImportDate"
    and "LifeOriginalEffectiveDateCompare"."ImportDataId" = "ImportData"."Id"
  )
where
  "ImportData"."CompanyId" = ?
  and "ImportData"."ImportDate" = ?
  and "LifeOriginalEffectiveDate"."LifeId" is not null
  and "LifeOriginalEffectiveDateCompare"."ImportDataId" is null