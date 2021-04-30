-- NOTE. This query does not limit by target, so you could get zero, one or a collection of
-- features over multiple targets depending on the feature type.
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
        left join "CompanyFeature" cf on
        (
                    cf."FeatureCode" = f."Code"
                and cf."CompanyId" = ?
            )
where
        f."CompanyFlg" = true
  and f."Code" = ?