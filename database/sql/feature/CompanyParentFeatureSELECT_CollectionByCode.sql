-- NOTE. This query does not limit by target, so you could get zero, one or a collection of
-- features over multiple targets depending on the feature type.
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
    )
where
  f."CompanyParentFlg" = true
  and f."Code" = ?