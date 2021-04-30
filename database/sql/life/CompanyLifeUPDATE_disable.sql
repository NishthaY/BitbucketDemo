update "CompanyLife"
    set
        "Enabled" = false 
where
    "CompanyLife"."Id" = ?
