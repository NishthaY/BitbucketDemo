select
    distinct("BeneficiaryNormalized") as "NormalizedToken"
from
     "CompanyBeneficiaryMap"
where
    "CompanyId" = ?
    and "ColumnCode" = ?