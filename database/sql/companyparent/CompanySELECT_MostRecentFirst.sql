select
    "Company"."Id" as "company_id"
     , "Company"."CompanyName" as "company_name"
     , "Company"."CompanyAddress" as "company_address"
     , "Company"."CompanyCity" as "company_city"
     , "Company"."CompanyState" as "company_state"
     , "Company"."CompanyPostal" as "company_postal"
     , "Company"."Enabled" as "enabled"
from
    "Company"
        join "HistoryChangeToCompany" on (
                "HistoryChangeToCompany"."CompanyId" = "Company"."Id"
            and "HistoryChangeToCompany"."UserId" = ?
        )
        join "CompanyParentCompanyRelationship" on ( "CompanyParentCompanyRelationship"."CompanyId" = "Company"."Id" )
where
        "Company"."Id" <> 1        -- Never show Advice2Pay company.
  and "CompanyParentCompanyRelationship"."CompanyParentId" = ?
order by
    "HistoryChangeToCompany"."ChangedToDate" desc
limit 5
