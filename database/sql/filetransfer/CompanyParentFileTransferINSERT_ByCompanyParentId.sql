insert into "CompanyParentFileTransfer" ( "CompanyParentId", "Username", "Hostname", "Port", "DestinationPath", "EncryptedPassword", "EncryptedSSHKey", "Protocol", "Enabled") values ( ?, ?, ?, ?, ?, ?, ?, 'SFTP', true )