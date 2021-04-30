select
  cc.*
  , cm."CarrierCode" as "BestMatch"
FROM
  "CompanyCarrier" cc
  join "CarrierMapping" cm on ( upper(cm."UserDescription") = upper(cc."UserDescription"))
WHERE
  cc."CompanyId" = ?
  and cc."CarrierCode" is null