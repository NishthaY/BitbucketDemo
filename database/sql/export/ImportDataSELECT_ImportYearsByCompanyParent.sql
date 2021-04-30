select
    extract(YEAR from data."ImportDate") as "Year"
from
    "ImportData" data
        join "CompanyParentCompanyRelationship" r on ( r."CompanyParentId" = ? )
where
        data."CompanyId" =  r."CompanyId"
group by extract(YEAR from data."ImportDate")
order by extract(YEAR from data."ImportDate") asc