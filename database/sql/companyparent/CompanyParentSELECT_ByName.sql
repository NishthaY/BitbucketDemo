select
    "Id"
    , "Name"
    , "Address"
    , "City"
    , "State"
    , "Postal"
    , "Enabled"
    , "Seats"
from
    "CompanyParent"
where
    "Name" = ?
order by "Id" desc
