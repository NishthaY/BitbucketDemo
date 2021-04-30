select
  *
from
  "CompanyParentFeature"
where
  "CompanyParentId" = ?
  and "FeatureCode" = ?
  and ( "Target" = ? OR "Target" is null )