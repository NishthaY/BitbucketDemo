select
	"CompanyCoverageTier"."Id" as "BestGuessId"
from
	"CompanyCoverageTier"
	join "AgeBand" on ( "AgeBand"."CompanyCoverageTierId" = "CompanyCoverageTier"."Id" )
where
	"CompanyCoverageTier"."CompanyId" = ?
	and "CompanyCoverageTier"."CarrierId" = ?
	and "CompanyCoverageTier"."PlanTypeId" = ?
	and "CompanyCoverageTier"."PlanId" = ?
order by "CompanyCoverageTier"."Id" asc
limit 1
