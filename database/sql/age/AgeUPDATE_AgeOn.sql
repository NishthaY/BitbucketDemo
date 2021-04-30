UPDATE "Age" set
	"AgeOn" =
		CASE
			WHEN "Age"."AgeTypeId" = 1 THEN format('%s/%s/%s', "Age"."AnniversaryMonth", "Age"."AnniversaryDay", ?)
			WHEN "Age"."AgeTypeId" = 2 AND "Age"."WashRule" is not null AND "Age"."WashRule"::int = 1 THEN to_char(last_day( to_date( format('%s/%s/%s', ?, "Age"."WashRule", ?), 'MM/DD/YYYY' )), 'MM/DD/YYYY')
			WHEN "Age"."AgeTypeId" = 2 AND "Age"."WashRule" is not null THEN format('%s/%s/%s', ?, "Age"."WashRule", ?)
			WHEN "Age"."AgeTypeId" = 3 THEN format('%s/%s/%s', date_part('month', "Age"."DateOfBirth"), date_part('day', "Age"."DateOfBirth"), ?)
			WHEN "Age"."AgeTypeId" = 4 THEN format('%s/%s/%s', date_part('month', "Age"."IssuedDate"), date_part('day', "Age"."IssuedDate"), date_part('year', "Age"."IssuedDate"))
			ELSE to_char("Age"."DateOfBirth", 'MM/DD/YYYY')
		END
WHERE
	"Age"."CompanyId" = ?
	and "Age"."ImportDate" = ?
    and "Age"."CoverageTierId" = ?
