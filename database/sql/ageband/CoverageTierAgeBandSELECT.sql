select
	"CompanyCoverageTier"."Id" as "CoverageTierId"
	, "CompanyCoverageTier"."AgeBandIgnored" as "Ignored"
from
	"CompanyCoverageTier"
where
	"CompanyId" = ?
	and "Id" = ?
