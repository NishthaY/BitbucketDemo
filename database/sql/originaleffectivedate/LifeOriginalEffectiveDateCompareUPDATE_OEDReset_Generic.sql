update "LifeOriginalEffectiveDateCompare" set
  "OEDReset" = true
  , "OldestLifePlanDiscoveryDate" = ?
  , "OldestLifePlanEffectiveDate" = ?
  , "Calculated-EffectiveDate" = ?
  , "Description" = ?
where
  "LifeOriginalEffectiveDateCompare"."LifeId" = ?
  and "LifeOriginalEffectiveDateCompare"."CarrierId" = ?
  and "LifeOriginalEffectiveDateCompare"."PlanTypeId" = ?
  and "LifeOriginalEffectiveDateCompare"."PlanId" = ?
  and "LifeOriginalEffectiveDateCompare"."CoverageTierId" = ?
  and "LifeOriginalEffectiveDateCompare"."Code" = ?