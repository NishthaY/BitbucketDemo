select
    distinct("BeneficiaryNormalized") as "NormalizedToken"
from
     "CompanyParentBeneficiaryMap"
where
    "CompanyParentId" = ?
    and "ColumnCode" = ?