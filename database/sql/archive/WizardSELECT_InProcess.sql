select
	"Company"."Id" as "CompanyId"
	, "Company"."CompanyName"
	, CASE
		WHEN "StartupComplete" <> true THEN 'USER: Choosing their start month.'
		WHEN "UploadComplete" <> true THEN 'USER: Ready to upload a file.'
		WHEN "ParsingComplete" <> true THEN 'A2P: Parsing the input file.'
		WHEN "MatchComplete" <> true THEN 'USER: Mapping upload data columns.'
		WHEN "ValidationComplete" <> true THEN 'A2P: Validating upload file against thier column mappings.'
		WHEN "CorrectionComplete" <> true THEN 'USER: Reviewing data validation issues.'
		WHEN "SavingComplete" <> true THEN 'A2P: Commiting and organizing the import data.'
		WHEN "RelationshipComplete" <> true THEN 'USER: Assigning relationships.'
		WHEN "LivesComplete" <> true THEN 'USER: Resolving duplicate lives.'
		WHEN "PlanReviewComplete" <> true THEN 'USER: Providing plan setting details.'
		WHEN "ReportGenerationComplete" <> true THEN 'A2P: Generating monthly reports for review.'
		WHEN "AdjustmentsComplete" <> true THEN 'USER: Adding a manual adjustment.'
		WHEN "Finalizing" <> true THEN 'USER: Reviewing thier generated reports.'
		ELSE ''
	END as "UserAction"
    , 'Button1' as "Button1"
from
	"Wizard"
	join "Company" on ( "Company"."Id" = "Wizard"."CompanyId" )
where
	"StartupComplete" <> true
	or "UploadComplete" <> true
	or "ParsingComplete" <> true
	or "MatchComplete" <> true
	or "ValidationComplete" <> true
	or "CorrectionComplete" <> true
	or "SavingComplete" <> true
	or "RelationshipComplete" <> true
	or "LivesComplete" <> true
	or "PlanReviewComplete" <> true
	or "ReportGenerationComplete" <> true
	or "AdjustmentsComplete" <> true
	or "Finalizing" <> true
order by "Company"."CompanyName" asc
