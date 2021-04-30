UPDATE "ImportData"
SET "EmployeeId" = "CompanyUniversalEmployee"."UniversalEmployeeId"
FROM "CompanyUniversalEmployee"
WHERE
  "ImportData"."CompanyId" = ?
  and "ImportData"."ImportDate" = ?
  and "ImportData"."CompanyId" = "CompanyUniversalEmployee"."CompanyId"
  and "ImportData"."EmployeeSSN" = "CompanyUniversalEmployee"."EmployeeSSN"
  and "ImportData"."EmployeeId" is null