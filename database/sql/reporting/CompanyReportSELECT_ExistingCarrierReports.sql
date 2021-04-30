select
    r.*
     , c."UserDescription" as "CarrierName"
     , c."CarrierCode"
from
    "CompanyReport" r
        join "CompanyCarrier" c on (c."Id" = r."CarrierId")
where
    r."CompanyId" = ?
    and r."ImportDate" = ?
    and r."ReportTypeId" = ?