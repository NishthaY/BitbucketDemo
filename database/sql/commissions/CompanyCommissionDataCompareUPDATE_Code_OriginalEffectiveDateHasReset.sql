update "CompanyCommissionDataCompare" set
  "Code" = ?
  , "Description" = ?
  , "TierChanged" = null
  , "VolumeChanged" = null
  , "MonthlyCostChanged" = null
  , "VolumeIncreased" = null
  , "MonthlyCostIncreased" = null
WHERE
  "CompanyId" = ?
  and "ImportDate" = ?
  and "OEDReset" = true
  and "Code" is null