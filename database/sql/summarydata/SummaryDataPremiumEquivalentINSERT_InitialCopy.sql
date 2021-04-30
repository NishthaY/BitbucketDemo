insert into "SummaryDataPremiumEquivalent" ( "Id", "PreparedDate", "CompanyId", "ImportDate", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId", "AgeBandId", "TobaccoUser", "Lives", "Volume", "Premium", "AdjustedLives", "AdjustedVolume", "AdjustedPremium" )
select
    "Id"
    , "PreparedDate"
    , "CompanyId"
    , "ImportDate"
    , "CarrierId"
    , "PlanTypeId"
    , "PlanId"
    , "CoverageTierId"
    , "AgeBandId"
    , "TobaccoUser"
    , "Lives"
    , "Volume"
    , "Premium"
    , "AdjustedLives"
    , "AdjustedVolume"
    , "AdjustedPremium"
from
    "SummaryData"
where
    "CompanyId" = ?
    and "ImportDate" = ?
