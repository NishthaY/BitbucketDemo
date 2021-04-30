select
    *
from
    "AgeBandCarrierDefault"
where
    "CarrierCode" = ?
    and "AgeBandTypeCode" = ?
order by "AgeBandStart" asc