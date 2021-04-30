select
    *
from
    "CompanyBeneficiaryMap"
where
    "CompanyId" = ?
    and "ColumnCode" = ?
    and "BeneficiaryNormalized" = upper(trim(?))