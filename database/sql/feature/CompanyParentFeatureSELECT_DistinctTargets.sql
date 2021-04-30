select
    distinct("Target") as "Target"
from
    "CompanyParentFeature"
where
        "CompanyParentId" = ?
  and "FeatureCode" = ?
  and "Enabled" = ?
  and "Target" is not null