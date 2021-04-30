select "identifier", "identifier_type", "name", "address", "city", "state", "postal", "enabled", "changedtodate" from
    (
        select
            "Company"."Id" as "identifier"
             , 'company' as "identifier_type"
             , "Company"."CompanyName" as "name"
             , "Company"."CompanyAddress" as "address"
             , "Company"."CompanyCity" as "city"
             , "Company"."CompanyState" as "state"
             , "Company"."CompanyPostal" as "postal"
             , "Company"."Enabled" as "enabled"
             , "HistoryChangeToCompany"."ChangedToDate" as "changedtodate"
        from
            "Company"
                join "HistoryChangeToCompany" on (
                        "HistoryChangeToCompany"."CompanyId" = "Company"."Id"
                    and "HistoryChangeToCompany"."UserId" = ?
                )
        where
                "Company"."Id" <> 1

        UNION

        select
            "CompanyParent"."Id" as "identifier"
             , 'companyparent' as "identifier_type"
             , "CompanyParent"."Name" as "name"
             , "CompanyParent"."Address" as "address"
             , "CompanyParent"."City" as "city"
             , "CompanyParent"."State" as "state"
             , "CompanyParent"."Postal" as "postal"
             , "CompanyParent"."Enabled" as "enabled"
             , "HistoryChangeToCompanyParent"."ChangedToDate" as "changedtodate"
        from
            "CompanyParent"
                join "HistoryChangeToCompanyParent" on (
                        "HistoryChangeToCompanyParent"."CompanyParentId" = "CompanyParent"."Id"
                    and "HistoryChangeToCompanyParent"."UserId" = ?
                )
    ) x
order by
    "changedtodate" desc
limit ?