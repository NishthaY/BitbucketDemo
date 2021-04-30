update "LifeOriginalEffectiveDateCompare" c set
  "Calculated-EffectiveDate" = "OldestLifePlanEffectiveDate"
  , "Description" = 'Assuming the oldest EF date between exiting related life plans'
WHERE
  c."CompanyId" = ?
  AND c."ImportDate" = ?
  AND ( c."Code" = 'NEW' OR c."Code" = 'EXISTING' )
  and c."OldestLifePlanEffectiveDate" is not null
  AND c."OldestLifePlanEffectiveDate" < c."Calculated-EffectiveDate"