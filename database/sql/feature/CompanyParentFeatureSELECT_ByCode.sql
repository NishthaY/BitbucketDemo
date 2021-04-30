select
    f."Code"
    , f."Description"
    , case when cpf."Enabled" is null then false else cpf."Enabled" end as "Enabled"
     , f."Targetable"
     , f."TargetType"
     , cpf."Target"
from
  "Feature" f
  left join "CompanyParentFeature" cpf on  (
      cpf."FeatureCode" = f."Code"
      and cpf."CompanyParentId" = ?
      and ( cpf."Target" = ? OR cpf."Target" is null )
    )
where
  f."CompanyParentFlg" = true
  and f."Code" = ?
  and ( f."TargetType" = ? OR f."TargetType" is null )