update "LifeOriginalEffectiveDateCompare" set
  "LostDate" = ?
  , "Description" = coalesce("LifeOriginalEffectiveDateCompare"."Description", '') || 'Using the import date as lost date because the coverage was missing this month. '
WHERE
  "CompanyId" = ?
  and "ImportDate" = ?
  and "Code" = 'MISSING'