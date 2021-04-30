select
    extract(YEAR from data."ImportDate") as "Year"
from
    "ImportData" data
where
        "CompanyId" = ?
group by extract(YEAR from data."ImportDate")
order by extract(YEAR from data."ImportDate") asc