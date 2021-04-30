insert into "CompanyCarrier" ( "CompanyId", "CarrierNormalized" )
select
	"ImportData"."CompanyId"
	, upper("ImportData"."Carrier")
from
	"ImportData"
	left join "CompanyCarrier" on (
		"CompanyCarrier"."CompanyId" = "ImportData"."CompanyId"
		and "CompanyCarrier"."CarrierNormalized" = upper("ImportData"."Carrier")
	)
where
	"ImportData"."CompanyId" = ?
	and "ImportData"."Finalized" = false
	and "CompanyCarrier"."CarrierNormalized" is null
group by "ImportData"."CompanyId", upper("ImportData"."Carrier")
