update "LifeOriginalEffectiveDateCompare" set
  "Calculated-EffectiveDate" = "OldestLifePlanEffectiveDate"
where "CompanyId" = ?
      and "ImportDate" = ?
      and "Code" <> 'NEW'
      and "OldestLifePlanEffectiveDate" < "Calculated-EffectiveDate"