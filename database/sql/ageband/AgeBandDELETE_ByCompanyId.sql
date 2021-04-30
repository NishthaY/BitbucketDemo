delete from "AgeBand" where "Id" in (
  select
    "AgeBand"."Id"
  from
    "AgeBand"
    join "CompanyCoverageTier" on ( "CompanyCoverageTier"."Id" = "AgeBand"."CompanyCoverageTierId" )
    join "Company" on ("Company"."Id" = "CompanyCoverageTier"."CompanyId")
  where
    "Company"."Id" = ?
)