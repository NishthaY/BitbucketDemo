select
    "Id" as "company_id"
    , "CompanyName" as "company_name"
    , "CompanyAddress" as "company_address"
    , "CompanyCity" as "company_city"
    , "CompanyState" as "company_state"
    , "CompanyPostal" as "company_postal"
    , "Enabled" as "enabled"
from
    "Company"
where
    "CompanyName" = ?
order by "Id" desc
