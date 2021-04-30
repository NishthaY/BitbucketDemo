-- Find all commission warnings that have an import data id that
-- map back to a zero dollar item.  Remove those.  Because we don't
-- show those on the commission detail report, we don't want to issue
-- any warnings about them either.
delete from "CompanyCommissionWarning" where "Id" in (
  select
    w."Id"
  from
    "CompanyCommissionWarning" w
    join "ImportData" d on ( w."ImportDataId" = d."Id" )
    left join "RelationshipData" r on ( r."ImportDataId" = d."Id" )
  where
    1=1
    and w."CompanyId" = ?
    and w."ImportDate" = ?
    and w."ImportDataId" is not null  -- If we can't map it back to an import id, show it.
    and coalesce(r."MonthlyCost", d."MonthlyCost") = 0
)