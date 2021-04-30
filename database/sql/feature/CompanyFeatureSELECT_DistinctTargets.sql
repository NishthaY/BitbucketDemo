select
    distinct("Target") as "Target"
from
     "CompanyFeature"
where
    "CompanyId" = ?
    and "FeatureCode" = ?
    and "Enabled" = ?
    and "Target" is not null