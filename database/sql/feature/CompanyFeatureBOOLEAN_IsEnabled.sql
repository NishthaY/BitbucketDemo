select
  CASE WHEN cf."Enabled" is null THEN false ELSE cf."Enabled" END as "Enabled"
from
  "Feature" f
  left join "CompanyFeature" cf on
  (
    cf."FeatureCode" = f."Code"
    and ( cf."Target" = ? OR cf."Target" is null )
  )
WHERE
  1=1
  and f."Code" = ?
  and f."CompanyFlg" = true
  and ( f."TargetType" = ? OR f."TargetType" is null )
  and cf."CompanyId" = ?