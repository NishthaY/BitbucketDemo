UPDATE "Age" SET
	"IssuedDate" = "ImportData"."CoverageStartDate"
FROM
	"ImportData"
WHERE
	"Age"."ImportDataId" = "ImportData"."Id"
	and "Age"."CompanyId" = ?
	and "Age"."ImportDate" = ?
