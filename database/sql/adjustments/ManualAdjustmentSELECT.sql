select
	"ManualAdjustment"."Id"
	, case when "ManualAdjustment"."Amount" >= 0 then 'Debit' else 'Credit' end as "Type"
	, "CompanyCarrier"."UserDescription" as "Carrier"
	, "ManualAdjustment"."Amount"
    , "ManualAdjustment"."Memo"
from
	"ManualAdjustment"
	join "CompanyCarrier" on
	(
		"CompanyCarrier"."CompanyId" = "ManualAdjustment"."CompanyId"
		and "CompanyCarrier"."Id" = "ManualAdjustment"."CarrierId"
	)
where
	1=1
	and "ManualAdjustment"."CompanyId" = ?
	and "ImportDate" = ?
order by "ManualAdjustment"."Id" asc
