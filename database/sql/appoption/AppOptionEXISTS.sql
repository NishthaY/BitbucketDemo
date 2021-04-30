select
    case when count(*) = 0 then 'f' else 't' end as "Exists"
from
    "AppOption"
where
    "Key" = ?
    
