-- Find each unique Many2Many  item and calculate the min/max CoverageStartDate
select
	"Before-CoverageTierIdList" as "BeforeCoverageTierIdList"
	, min("CoverageStartDate") as "MinCoverageStartDate"
	, max("CoverageStartDate") as "MaxCoverageStartDate"
from
	"RetroDataLifeEvent"
where
	"CompanyId" = ?
	and "ImportDate" = ?
	and "Before-CoverageTierIdList" like '%,%'
group by "Before-CoverageTierIdList"
