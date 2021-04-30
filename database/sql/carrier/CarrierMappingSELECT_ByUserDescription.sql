select
    "CarrierCode"
from
    "CarrierMapping"
where
    upper("UserDescription") = trim(upper( ? ))