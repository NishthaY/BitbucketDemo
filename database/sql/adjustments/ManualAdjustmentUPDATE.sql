update "ManualAdjustment" set
    "CarrierId" = ?
    , "Memo" = ?
    , "Amount" = ?
where
    "CompanyId" = ?
    and "ImportDate" = ?
    and "Id" = ?
