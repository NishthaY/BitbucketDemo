insert into "ImportDataDuplicateLives" ( "CompanyId", "ImportDate", "Count", "EmployeeId", "SSN", "FirstName", "DateOfBirth", "Relationship", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierId" )
  SELECT
    "ImportData"."CompanyId",
    "ImportData"."ImportDate",
    count(*) as "Count",
    "ImportData"."EmployeeId",
    "ImportData"."SSN",
    "ImportData"."FirstName",
    "ImportData"."DateOfBirth",
    "ImportData"."Relationship",
    "WashedData"."CarrierId",
    "WashedData"."PlanTypeId",
    "WashedData"."PlanId",
    "WashedData"."CoverageTierId"
  FROM
    "ImportData"
    JOIN "ImportLife" ON ("ImportLife"."ImportDataId" = "ImportData"."Id")
    JOIN "WashedData" ON ("WashedData"."ImportDataId" = "ImportData"."Id")
  WHERE
    "ImportData"."CompanyId" = ?
    AND "ImportData"."ImportDate" = ?
  GROUP BY
    "ImportData"."CompanyId"
    , "ImportData"."ImportDate"
    , "ImportData"."EmployeeId"
    , "ImportData"."SSN"
    , "ImportData"."FirstName"
    , "ImportData"."DateOfBirth"
    , "ImportData"."Relationship"
    , "WashedData"."CarrierId"
    , "WashedData"."PlanTypeId"
    , "WashedData"."PlanId"
    , "WashedData"."CoverageTierId"
  HAVING count(*) > 1