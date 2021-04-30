update "RetroDataLifeEvent"
set "LifeEvent" = subquery."LifeEvent"
from ( select * from "LifeEventCompare" where "CompanyId" = ? and "ImportDate" = ? ) subquery
where
	"RetroDataLifeEvent"."CompanyId" = subquery."CompanyId"
	and "RetroDataLifeEvent"."ImportDate" = subquery."ImportDate"
	and "RetroDataLifeEvent"."PlanId" = subquery."PlanId"
	and "RetroDataLifeEvent"."CoverageTierId" = subquery."CoverageTierId"
	and "RetroDataLifeEvent"."CoverageStartDate" = subquery."CoverageStartDate"
	and "RetroDataLifeEvent"."Before-CoverageStartDateList" = subquery."Before-CoverageStartDateList"
	and "RetroDataLifeEvent"."MonthlyCost" = subquery."MonthlyCost"
	and "RetroDataLifeEvent"."Before-MonthlyCost" = subquery."Before-MonthlyCost"
	and "RetroDataLifeEvent"."Volume" = subquery."Volume"
	and "RetroDataLifeEvent"."Before-Volume" = subquery."Before-Volume"
