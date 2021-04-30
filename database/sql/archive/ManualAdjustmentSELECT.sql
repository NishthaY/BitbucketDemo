select
	"CompanyCarrier"."UserDescription" as "Carrier"
	, "ManualAdjustment"."Memo"
	, "ManualAdjustment"."Amount"
from
	"ManualAdjustment"
	join "CompanyCarrier" on ( "ManualAdjustment"."CarrierId" = "CompanyCarrier"."Id")
where
	"ManualAdjustment"."CompanyId" = ?
	and "ManualAdjustment"."ImportDate" = ?
