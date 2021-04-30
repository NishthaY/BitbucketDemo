select
	TO_CHAR(r1."ImportDate", 'Month YYYY') as import_date
	, TO_CHAR(r1."ImportDate", 'YYYYMM') as date_tag
	, false as draft_flg
	, r1."CompanyId" as company_id
from
	"CompanyReport" as r1
where
	r1."CompanyId" = ?
	and r1."ImportDate" <> ?
group by r1."ImportDate", r1."CompanyId"
order by r1."ImportDate" desc
