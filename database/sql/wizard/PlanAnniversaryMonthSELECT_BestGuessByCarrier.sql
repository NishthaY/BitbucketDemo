select
    "PlanAnniversaryMonth"
from
    "CompanyPlanType"
    join "CompanyCarrier" on ( "CompanyCarrier"."Id" = "CompanyPlanType"."CarrierId" )
where
    "CompanyPlanType"."CompanyId" = ?
    and "CompanyCarrier"."CarrierNormalized" = upper(?)
    and "PlanAnniversaryMonth" is not null
group by "CompanyPlanType"."PlanAnniversaryMonth"
order by count(*) desc, "CompanyPlanType"."PlanAnniversaryMonth" asc
limit 1
