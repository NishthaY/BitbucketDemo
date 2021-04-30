update "CompanyCommissionDataCompare" set
  "Code" = ?
  , "Description" = ?
WHERE
  "CompanyId" = ?
  and "ImportDate" = ?
  and "Code" is null
  and "OEDReset" = false
  and ( "VolumeChanged" = true OR "TierChanged" = true )
  and "MonthlyCostChanged" = true
  and "MonthlyCostIncreased" = false
  and "Code" is null