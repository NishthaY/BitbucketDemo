select
    f."Code"
    , f."Description"
    , case when cf."Enabled" is null then false else cf."Enabled" end as "Enabled"
    , CASE WHEN f."CompanyParentFlg" = true THEN true ELSE false END as "ChildFlg"
    , f."Targetable"
    , f."TargetType"
    , cf."Target"
from
    "Feature" f
    left join "CompanyFeature" cf on  ( cf."FeatureCode" = f."Code" and cf."CompanyId" = ?)
where
    f."CompanyFlg" = true
order by
    f."Code" asc