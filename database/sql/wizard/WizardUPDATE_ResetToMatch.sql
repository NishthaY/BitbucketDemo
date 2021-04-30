update "Wizard" set
    "MatchComplete" = false
    , "ValidationComplete" = false
    , "CorrectionComplete" = false
    , "SavingComplete" = false
    , "RelationshipComplete" = false
    , "PlanReviewComplete" = false
    , "ReportGenerationComplete" = false
    , "AdjustmentsComplete" = true
where "CompanyId" =?
