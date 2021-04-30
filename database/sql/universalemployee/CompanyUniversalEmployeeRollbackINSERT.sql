insert into "CompanyUniversalEmployeeRollback" ( "CompanyId", "ImportDate", "ImportDataId", "OriginalEmployeeId" )
select
  d."CompanyId"
  , d."ImportDate"
  , d."Id"
  , d."EmployeeId"
from
  "ImportData" d
where
  d."CompanyId" = ?
  and d."ImportDate" = ?
  and d."EmployeeId" is null