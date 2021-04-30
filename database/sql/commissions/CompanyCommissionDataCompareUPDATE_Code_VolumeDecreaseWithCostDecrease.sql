update "CompanyCommissionDataCompare" set
  "Code" = ?
  , "Description" = ?
WHERE
  "CompanyId" = ?
  and "ImportDate" = ?
  and "Code" is null
  and "OEDReset" = false
  and "VolumeChanged" = false
  and "MonthlyCostChanged" = true
  and "MonthlyCostIncreased" = false
  and "Code" is null