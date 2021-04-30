select
    sum(data."Premium" + data."AdjustedPremium") as "TotalAdjustedPremium"
from
    "SummaryData" data
        join "CompanyCarrier" on ( "CompanyCarrier"."Id" = data."CarrierId" )
where
        data."CompanyId" = ?
        and data."CarrierId" = ?
        and data."ImportDate" = ?
