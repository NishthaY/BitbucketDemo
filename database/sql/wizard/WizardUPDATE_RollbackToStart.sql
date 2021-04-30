update "Wizard" set
        "UploadComplete" = false
        , "ParsingComplete" = false
        , "MatchComplete" = false
        , "ValidationComplete" = false
        , "CorrectionComplete" = false
        , "SavingComplete" = false
        , "PlanReviewComplete" = false
        , "ReportGenerationComplete" = false
        , "Finalizing" = false
        , "UploadFile" = null
where "CompanyId" = ?
