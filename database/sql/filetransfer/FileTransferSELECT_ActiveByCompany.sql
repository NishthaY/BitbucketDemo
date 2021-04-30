-- Company Transfers
select
    "CompanyFileTransfer"."Id"
    ,"CompanyFileTransfer"."Hostname"
    ,"CompanyFileTransfer"."Username"
    ,"CompanyFileTransfer"."EncryptedPassword"
    ,"CompanyFileTransfer"."EncryptedSSHKey"
    ,"CompanyFileTransfer"."Protocol"
    ,"CompanyFileTransfer"."Port"
    ,"CompanyFileTransfer"."DestinationPath"
    ,"CompanyFileTransfer"."Enabled"
    ,null as "CompanyParentId"
    ,"CompanyFileTransfer"."CompanyId"
from
    "CompanyFileTransfer"
where
    "CompanyFileTransfer"."CompanyId" = ?
    and "CompanyFileTransfer"."Enabled" = true


UNION ALL


-- CompanyParent Transfers
select
    "CompanyParentFileTransfer"."Id"
    ,"CompanyParentFileTransfer"."Hostname"
    ,"CompanyParentFileTransfer"."Username"
    ,"CompanyParentFileTransfer"."EncryptedPassword"
    ,"CompanyParentFileTransfer"."EncryptedSSHKey"
    ,"CompanyParentFileTransfer"."Protocol"
    ,"CompanyParentFileTransfer"."Port"
    ,"CompanyParentFileTransfer"."DestinationPath"
    ,"CompanyParentFileTransfer"."Enabled"
    ,"CompanyParentFileTransfer"."CompanyParentId" as "CompanyParentId"
    ,null as "CompanyId"
from
    "Company"
    join "CompanyParentCompanyRelationship" on ( "CompanyParentCompanyRelationship"."CompanyId" = "Company"."Id" )
    join "CompanyParent" on ( "CompanyParent"."Id" = "CompanyParentCompanyRelationship"."CompanyParentId")
    join "CompanyParentFileTransfer" on ( "CompanyParentFileTransfer"."CompanyParentId" = "CompanyParent"."Id" )
where
    "Company"."Id" = ?
    and "CompanyParentFileTransfer"."Enabled" = true
