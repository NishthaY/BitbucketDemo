UPDATE
	"Age"
SET
	"Age"=extract( year from age("Age"."AgeOn"::date, "Age"."DateOfBirth"))::int
	, "AgeDescription" =
		CASE
			WHEN "Age"."AgeOn" is null then 'WARNING:Unable to calculate age.'
			WHEN EXTRACT(YEAR FROM "Age"."AgeOn"::date) - EXTRACT(YEAR from "Age"."DateOfBirth") < 0 THEN format('WARNING:Date of birth is after %s.',"Age"."AgeOn")
		ELSE null
	END
WHERE
	"Age"."CompanyId" = ?
	and "Age"."ImportDate" = ?
