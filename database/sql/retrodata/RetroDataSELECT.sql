select
	*
from
	"RetroData"
where
	"CompanyId" = ?
	and "ImportDate" = ?
	and "AdjustmentType" is not null
