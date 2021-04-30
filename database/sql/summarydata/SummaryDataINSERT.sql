insert into "SummaryData"
(
    "CompanyId"
    , "ImportDate"
    , "CarrierId"
    , "PlanTypeId"
    , "PlanId"
    , "CoverageTierId"
    , "AgeBandId"
    , "TobaccoUser"
    , "PreparedDate"
)
 values
 (
     ?,?,?,?,?,?,?,?, to_date(to_char( now() , 'MM/DD/YYYY'), 'MM/DD/YYYY')
 )
