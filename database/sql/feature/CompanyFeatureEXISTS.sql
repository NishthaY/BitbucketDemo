select
  *
from
  "CompanyFeature"
where
  "CompanyId" = ?
  and "FeatureCode" = ?
  and ( "Target" = ? OR "Target" is null )