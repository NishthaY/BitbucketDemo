select
    "ParentCarrierId"
    , "CarrierId"
from
    "SummaryDataPremiumEquivalent"
where
    "CompanyId" = ?
    and "ImportDate" = ?
    and "CarrierId" = ?
group by "ParentCarrierId", "CarrierId"
