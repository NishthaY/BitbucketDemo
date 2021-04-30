update "CompanyCommissionLifeResearch" set
  "ImportDataId" = -1
where
  "CompanyId" = ?
  and "ImportDate" = ?
  and "ImportDataId" is null