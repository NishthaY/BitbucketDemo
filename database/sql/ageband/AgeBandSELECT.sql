select
	*
from
	"AgeBand"
where
	"AgeBand"."CompanyCoverageTierId" = ?
order by "AgeBand"."AgeBandStart" asc
