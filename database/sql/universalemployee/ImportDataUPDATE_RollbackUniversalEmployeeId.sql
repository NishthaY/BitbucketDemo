UPDATE "ImportData"
SET "EmployeeId" = "CompanyUniversalEmployeeRollback"."OriginalEmployeeId"
FROM "CompanyUniversalEmployeeRollback"
WHERE
  "ImportData"."CompanyId" = ?
  and "ImportData"."ImportDate" = ?
  and "CompanyUniversalEmployeeRollback"."CompanyId" = "ImportData"."CompanyId"
  and "CompanyUniversalEmployeeRollback"."ImportDate" = "ImportData"."ImportDate"
  and "CompanyUniversalEmployeeRollback"."ImportDataId" = "ImportData"."Id"