insert into "LifeOriginalEffectiveDateCompare" ( "ImportDataId", "CompanyId", "ImportDate", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId", "CoverageStartDate", "OriginalEffectiveDate", "Calculated-EffectiveDate", "IsCoverageStartDate", "Code", "Description" )
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
  , CASE
    WHEN ( "ImportData"."OriginalEffectiveDate" is not null AND "ImportData"."ImportDate" = ? ) THEN "ImportData"."OriginalEffectiveDate"
    ELSE "ImportData"."CoverageStartDate"
    END as "Calculated-EffectiveDate"
  , CASE
    WHEN ( "ImportData"."OriginalEffectiveDate" is not null AND "ImportData"."ImportDate" = ? ) THEN false
    ELSE true
    END as "IsCoverageStartDate"
  , 'NEW' as "Code"
  , 'Calculated the effective date based on import data because this is a new item. ' as "Description"
from
  "ImportData"
  join "LifeData" on ( "ImportData"."Id" = "LifeData"."ImportDataId" )
  join "WashedData" on ( "WashedData"."ImportDataId" = "ImportData"."Id" )
  left join "LifeOriginalEffectiveDate" on ( "LifeOriginalEffectiveDate"."CarrierId" = "WashedData"."CarrierId" and "LifeOriginalEffectiveDate"."PlanTypeId" = "WashedData"."PlanTypeId" and "LifeOriginalEffectiveDate"."PlanId" = "WashedData"."PlanId" and "LifeOriginalEffectiveDate"."CoverageTierId" = "WashedData"."CoverageTierId" and "LifeOriginalEffectiveDate"."LifeId" = "LifeData"."LifeId" )
where
  "ImportData"."CompanyId" = ?
  and "ImportData"."ImportDate" = ?
  and "LifeOriginalEffectiveDate"."CarrierId" is null