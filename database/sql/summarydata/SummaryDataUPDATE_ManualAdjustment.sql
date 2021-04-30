update "SummaryData" set
	"AdjustedVolume"=?
	, "AdjustedPremium"=?
where
	"CompanyId"=?
	and "ImportDate" = ?
	and "CarrierId" = ?
	and "PlanTypeId" {PLANTYPEID}
	and "PlanId" {PLANID}
	and "CoverageTierId" {COVERAGETIERID}
	and "AgeBandId" {AGEBAND}
	and "TobaccoUser" {TOBACCOUSER}
