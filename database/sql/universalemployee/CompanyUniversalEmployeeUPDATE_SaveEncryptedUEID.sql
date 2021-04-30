update "CompanyUniversalEmployee" set
  "UniversalEmployeeId" = ?
  , "Finalized" = true
where
  "CompanyId" = ?
  and "DiscoveryDate" = ?
  and "Id" = ?