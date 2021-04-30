insert into "ImportLife" ( "ImportDataId", "CompanyId", "ImportDate", "LifeKey" )
select
  import."Id"
  , import."CompanyId"
  , import."ImportDate"
  ,format
  (
    '%s::DELIM::%s::DELIM::%s::DELIM::%s::DELIM::%s'
    , import."EmployeeId"
    , import."SSN"
    , upper(import."FirstName")
    , to_char(import."DateOfBirth"::date, 'YYYYMMDD')
    , import."Relationship"
  ) as "LifeKey"
from "ImportData" import
  left join "ImportLife" life on
  (
    import."Id" = life."ImportDataId"
  )
WHERE
  import."CompanyId" = ?
  and import."ImportDate" = ?
  and life."ImportDataId" is null