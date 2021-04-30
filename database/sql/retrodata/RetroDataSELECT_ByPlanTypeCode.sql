
select
	"CompanyId"
	, "ImportDate" as "TargetDate"
	, "CarrierId"
	, "PlanTypeId"
	, "PlanId"
	, "CoverageTierId"
	, "LifeId"
	, "Id" as "RetroId"
from
	"RetroData"
where
    "CompanyId" = ?
    and "ImportDate" = ?
	and "PlanTypeCode" = ?
	and "CoverageTierId" in ( {LIST} )
	and "LifeId" = ?
