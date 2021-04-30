select
    "AnniversaryMonth"
    , "AnniversaryDay"
    , "AgeTypeId"
from
    "AgeBand"
where
    "CompanyCoverageTierId" = ? 
limit 1
