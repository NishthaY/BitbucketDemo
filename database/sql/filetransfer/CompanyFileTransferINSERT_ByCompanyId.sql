insert into "CompanyFileTransfer" ( "CompanyId", "Username", "Hostname", "Port", "DestinationPath", "EncryptedPassword", "EncryptedSSHKey", "Protocol", "Enabled") values ( ?, ?, ?, ?, ?, ?, ?, 'SFTP', true )