insert into "ReportReviewWarnings" ( "CompanyId", "ImportDataId", "ImportDate", "Issue" )
    select
    	"Age"."CompanyId"
    	, "Age"."ImportDataId"
    	, "Age"."ImportDate"
    	, trim(leading 'WARNING:' from "Age"."AgeDescription" )
    from
    	"Age"
    where
    	"CompanyId" = ?
    	and "ImportDate" = ?
    	and "AgeDescription" like 'WARNING:%'
