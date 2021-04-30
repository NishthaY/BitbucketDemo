update "RetroDataLifeEvent" set
    "LifeEvent" = false
    , "DefaultType" = 'retro'
where
    "CompanyId" = ?
    and "ImportDate" = ?
    and "AutoSelected" = false
    and "LifeEvent" is null