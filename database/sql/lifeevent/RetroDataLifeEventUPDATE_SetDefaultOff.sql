update "RetroDataLifeEvent" set
    "DefaultType" = null
where
    "CompanyId" = ?
    and "ImportDate" = ?
    and "Id" = ?