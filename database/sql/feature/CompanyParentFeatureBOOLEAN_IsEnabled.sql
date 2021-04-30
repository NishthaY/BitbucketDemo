select
    CASE WHEN cpf."Enabled" is null THEN false ELSE cpf."Enabled" END as "Enabled"
from
    "Feature" f
    left join "CompanyParentFeature" cpf on
    (
        cpf."FeatureCode" = f."Code"
        and ( cpf."Target" = ? OR cpf."Target" is null )
    )
WHERE
    1=1
    and f."Code" = ?
    and f."CompanyParentFlg" = true
    and ( f."TargetType" = ? OR f."TargetType" is null )
    and ( cpf."CompanyParentId" = ? OR cpf."CompanyParentId" is null )



--select
--    CASE WHEN cpf."Enabled" is null THEN false ELSE cpf."Enabled" END as "Enabled"
--from
--    "Feature" f
--        left join "CompanyParentFeature" cpf on ( cpf."FeatureCode" = f."Code" )
--WHERE
--        1=1
--  and f."Code" = ?
--  and f."CompanyParentFlg" = true
--  and ( cpf."CompanyParentId" = ? OR cpf."CompanyParentId" is null )
