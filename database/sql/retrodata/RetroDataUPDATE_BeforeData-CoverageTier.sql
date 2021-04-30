update "RetroData" set
    "CoverageTierKey" = ?
    , "Before-CoverageTierKey"=?
    , "Before-CoverageStartDate"=?
    , "Before-Volume"=?
    , "Before-MonthlyCost"=?
    , "Before-CoverageStartDateList"=?
    , "Before-PlanId"=?
    , "Before-PlanList"=?
where
    "CompanyId" = ?
    and "ImportDate" = ?
    and "LifeId" = ?
    and "PlanTypeCode" = ?
