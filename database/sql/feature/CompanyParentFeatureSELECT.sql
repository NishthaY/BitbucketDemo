select
  f."Code"
  , f."Description"
  , case when cpf."Enabled" is null then false else cpf."Enabled" end as "Enabled"
  , false as "ChildFlg"
     , f."Targetable"
     , f."TargetType"
     , cpf."Target"
from
  "Feature" f
  left join "CompanyParentFeature" cpf on  ( cpf."FeatureCode" = f."Code" and cpf."CompanyParentId" = ?)
where
  f."CompanyParentFlg" = true
order by
  f."Code" asc