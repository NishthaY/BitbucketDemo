select
    "Id"
     , "Identifier"
     , "IdentifierType"
     , "Created" at time zone '{PREFERED_TIMEZONE}' as "Created"
     , "Status"
     , "Modified" at time zone '{PREFERED_TIMEZONE}' as "Modified"
from
    "Export"
where
        "Identifier" = ?
  and "IdentifierType" = ?
order by "Created" desc