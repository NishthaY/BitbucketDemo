select
    *
from
    "CompanyParentBeneficiaryMap"
where
    "CompanyParentId" = ?
    and "ColumnCode" = ?
    and "BeneficiaryNormalized" = upper(trim(?))