select
    TO_CHAR(r1."ImportDate", 'Month YYYY') as description
     , TO_CHAR(r1."ImportDate", 'YYYYMM') as date_tag
     , r1."CompanyId" as company_id
from
    "SupportTimer" as r1
where
        r1."CompanyId" = ?
group by r1."ImportDate", r1."CompanyId"
order by r1."ImportDate" desc