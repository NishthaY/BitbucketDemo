delete from
    "RetroDataLifeEventWarning"
where
    "CompanyId" = ?
    and "ImportDate" = ?
    and "IssueType" = ?
    and "ImportDataId" =
    (
        select
            r."ImportDataId"
        from
            "RetroDataLifeEvent" e
            join "RetroData" r on ( r."Id" = e."RetroDataId")
        where
            e."Id" = ?
    )
