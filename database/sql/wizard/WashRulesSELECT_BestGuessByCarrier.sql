select
    "WashRule"
from
    "CompanyPlanType"
    join "CompanyCarrier" on ( "CompanyCarrier"."Id" = "CompanyPlanType"."CarrierId" )
where
    "CompanyPlanType"."CompanyId" = ?
    and "CompanyCarrier"."CarrierNormalized" = upper(?)
    and "WashRule" is not null
group by "CompanyPlanType"."WashRule"
order by count(*) desc, "CompanyPlanType"."WashRule" asc
limit 1
