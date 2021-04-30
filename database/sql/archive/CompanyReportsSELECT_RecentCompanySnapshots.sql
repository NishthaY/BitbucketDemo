select
	TO_CHAR(r1."ImportDate", 'Month YYYY') as import_date
from
	"CompanyReport" as r1
where
	r1."CompanyId" = ?
group by r1."ImportDate", r1."CompanyId"
order by r1."ImportDate" desc
limit 3
