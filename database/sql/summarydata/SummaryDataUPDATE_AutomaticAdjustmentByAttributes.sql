update "SummaryData" set
	"AdjustedVolume"=?
	, "AdjustedPremium"=?
where
	"CompanyId"=?
	and "ImportDate" = ?
	and "CarrierId" = ?
	and "PlanTypeId" = ?
	and "PlanId" = ?
	and "CoverageTierId" = ?
	and "AgeBandId" {AGEBAND}
	and "TobaccoUser" {TOBACCOUSER}
