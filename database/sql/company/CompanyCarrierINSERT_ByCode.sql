insert into "CompanyCarrier" ( "CompanyId", "CarrierNormalized", "UserDescription", "CarrierCode" )
select
    ? as "CompanyId"
  , trim(upper("UserDescription")) as "CarrierNormalized"
  , "UserDescription" as "UserDescription"
  , "CarrierCode" as "CarrierCode"
from
  "Carrier"
where
  "CarrierCode" = ?