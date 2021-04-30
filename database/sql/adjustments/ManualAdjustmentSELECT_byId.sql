select
    "ManualAdjustment"."Id"
	, "CompanyCarrier"."Id" as "CarrierId"
    , case when "ManualAdjustment"."Amount" >= 0 then 'Credit' else 'Debit' end as "Type"
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
	"ManualAdjustment"."CompanyId" = ?
	and "ImportDate" = ?
    and "ManualAdjustment"."Id" = ?
order by "ManualAdjustment"."Id" asc
