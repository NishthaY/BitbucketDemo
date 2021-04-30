insert into "CompanyUniversalEmployee" ( "CompanyId", "EmployeeSSN", "UniversalEmployeeId", "DiscoveryDate", "Finalized" )
  select
    d."CompanyId"
    , d."EmployeeSSN"
    , uuid_generate_v4() as "UniversalEmployeeId"
    , d."ImportDate"
    , false as "Finalized"
  from
    "ImportData" d
    left join "CompanyUniversalEmployee" cue on ( cue."CompanyId" = d."CompanyId" and cue."EmployeeSSN" = d."EmployeeSSN" )
  where
    d."CompanyId" = ?
    and d."ImportDate" = ?
    and d."EmployeeId" is null
    and cue."UniversalEmployeeId" is null
  group by d."CompanyId", d."ImportDate", d."EmployeeSSN"