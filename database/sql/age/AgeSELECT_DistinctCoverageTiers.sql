select
    distinct("CoverageTierId") as "CoverageTierId"
from
    "Age"
where
    "Age"."CompanyId" = ?
    and "Age"."ImportDate" = ?
