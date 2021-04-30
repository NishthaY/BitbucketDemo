-- DELETE the SummaryData records that should live in the SummaryDataPremiumEquivalent table.
delete
from
	"SummaryData" sd
	using "CompanyPlan"
where
	sd."CompanyId" = ?
	and sd."ImportDate" = ?
	and sd."CarrierId" = ?
	and sd."PlanTypeId" = ?
	and sd."PlanId" = "CompanyPlan"."Id"
	and "CompanyPlan"."PremiumEquivalent" = true
