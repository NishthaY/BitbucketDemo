select
    "RetroRule"
from
    "CompanyPlanType"
    join "CompanyCarrier" on ( "CompanyCarrier"."Id" = "CompanyPlanType"."CarrierId" )
where
    "CompanyPlanType"."CompanyId" = ?
    and "CompanyCarrier"."CarrierNormalized" = upper(?)
    and "RetroRule" is not null
group by "CompanyPlanType"."RetroRule"
order by count(*) desc, "CompanyPlanType"."RetroRule" asc
limit 1
