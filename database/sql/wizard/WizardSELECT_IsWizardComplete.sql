select
    CASE when count(*) >= 1 THEN true ELSE false END as complete
from "Wizard"
where
    "CompanyId" = ?
    and "UploadComplete" = true
    and "ParsingComplete" = true
    and "MatchComplete" = true
    and "ValidationComplete" = true
    and "CorrectionComplete" = true
    and "SavingComplete" = true
    and "PlanReviewComplete" = true
    and "ReportGenerationComplete" = true
    and "AdjustmentsComplete" = true
    and "Finalizing" = true
