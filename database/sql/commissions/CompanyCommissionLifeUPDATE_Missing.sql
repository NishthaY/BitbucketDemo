update "CompanyCommissionLife" set
  "ImportDataId" = null
where
  "CompanyId" = ?
  and "ImportDate" = ?
  and "ImportDataId" = -1