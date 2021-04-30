update "RetroDataLifeEvent" set
    "LifeEvent" = true
    , "DefaultType" = 'ignore'
where
    "CompanyId" = ?
    and "ImportDate" = ?
    and "AutoSelected" = false
    and "LifeEvent" is null