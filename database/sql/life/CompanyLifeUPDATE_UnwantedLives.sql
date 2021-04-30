update "CompanyLife" set
    "Enabled" = false
from
    "ImportLifeWorker" work
where
        work."CompanyId" = ?
  and work."ImportDate" = ?
  and work."TargetId" = "CompanyLife"."Id"