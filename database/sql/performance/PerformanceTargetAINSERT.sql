-- This is a comment ? with a questin mark
insert into "PerformanceTargetA" ( "CompanyId", "ImportDate", "SourceDataId", "DATA" )
select
  s."CompanyId"
  , s."ImportDate"
  , s."Id" as "SourceDataId"
  , s."DATA"
from
  "PerformanceSource" s
  left join "PerformanceTargetA" t on ( t."CompanyId" = s."CompanyId" and t."ImportDate" = s."ImportDate" and t."SourceDataId" = s."Id" )
WHERE
  s."CompanyId" = ?
  and s."ImportDate" = ?
  and t."Id" is null