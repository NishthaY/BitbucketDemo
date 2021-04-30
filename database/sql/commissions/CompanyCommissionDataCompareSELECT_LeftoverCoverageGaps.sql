select
    distinct("CoverageGapOffset") as "Offset"
from
     "CompanyCommissionDataCompare"
where
    "CompanyId" = ?
    and "ImportDate" = ?