update "CompanyParentFileTransfer"
  set "Hostname" = ?
  , "Username" = ?
  , "DestinationPath" = ?
  , "Port" = ?
  , "EncryptedPassword" = ?
  , "EncryptedSSHKey" = ?
WHERE "CompanyParentId" = ?