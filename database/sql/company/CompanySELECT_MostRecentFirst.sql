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
where
	"Company"."Id" <> 1
order by
	"HistoryChangeToCompany"."ChangedToDate" desc
limit 5
