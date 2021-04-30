select
	d."CompanyId"
	, d."ImportDate"
	, d."CarrierId"
	, d."PlanTypeId"
	, d."PlanId"
	, d."CoverageTierId"
	, d."AgeBandId"
	, d."TobaccoUser"
 from
    "SummaryData" as d
 where
 	"CompanyId" = ?
	and "ImportDate" = ?
    and "TobaccoUser" is not NULL
