select
    case when count(*) > 0 then true else false end as "InReportReview"
from
    "SummaryData" sd
    left join "Wizard" w on ( w."CompanyId" = sd."CompanyId" )
where
    sd."CompanyId" = ?
    and sd."ImportDate" = ?
    and w."ReportGenerationComplete" = true