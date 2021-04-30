update "CompanyFileTransfer"
  set "Hostname" = ?
  , "Username" = ?
  , "DestinationPath" = ?
  , "Port" = ?
  , "EncryptedPassword" = ?
  , "EncryptedSSHKey" = ?
WHERE "CompanyId" = ?