select
	"CompanyCoverageTier"."Id" as "CoverageTierId"
	, "CompanyCoverageTier"."TobaccoIgnored" as "Ignored"
from
	"CompanyCoverageTier"
where
	"CompanyId" = ?
	and "Id" = ?
