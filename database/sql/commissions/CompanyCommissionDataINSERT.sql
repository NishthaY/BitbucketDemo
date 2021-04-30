insert into "CompanyCommissionData" ( "CompanyId", "ImportDate", "LifeId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId", "MonthlyCost", "Volume", "OEDCode", "Calculated-EffectiveDate", "CoverageStartDate", "LostDate", "Before-CoverageStartDate", "OEDReset")
select
  c."CompanyId"
  ,c."ImportDate"
  ,c."LifeId"
  ,c."CarrierId"
  ,c."PlanTypeId"
  ,c."PlanId"
  ,c."CoverageTierId"
  , coalesce(rel."MonthlyCost", r."MonthlyCost") as "MonthlyCost"
  , coalesce(rel."Volume", r."Volume") as "Volume"
  ,c."Code" as "OEDCode"
  ,oed."EffectiveDate"
  ,c."CoverageStartDate"
  ,c."LostDate"
  , case
      WHEN r."AdjustmentType" = 4 THEN r."Before-CoverageStartDate"
      WHEN r."AdjustmentType" = 5 THEN r."Before-CoverageStartDate"
      WHEN r."AdjustmentType" = 6 THEN r."Before-CoverageStartDate"
    else null END as "Before-CoverageStartDate"
  , coalesce( c."OEDReset", false, c."OEDReset") as "OEDReset"
from
  "LifeOriginalEffectiveDateCompare" c
  left join "RetroData" r on ( r."CompanyId" = c."CompanyId" and r."ImportDate" = c."ImportDate" and r."LifeId" = c."LifeId" and r."CarrierId" = c."CarrierId" and r."PlanTypeId" = c."PlanTypeId" and r."PlanId" = c."PlanId" and r."CoverageTierId" = c."CoverageTierId")
  join "RelationshipData" rel on ( rel."ImportDataId" = r."ImportDataId")
  join "LifeOriginalEffectiveDate" oed on ( oed."LifeId" = c."LifeId" and oed."CarrierId" = c."CarrierId" and oed."PlanTypeId" = c."PlanTypeId" and oed."PlanId" = c."PlanId" and oed."CoverageTierId" = c."CoverageTierId" )
where
  c."CompanyId" = ?
  and c."ImportDate" = ?