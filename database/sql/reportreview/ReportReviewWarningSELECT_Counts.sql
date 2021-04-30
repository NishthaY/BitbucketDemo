select
  ? as "Company",
  (
    SELECT count(*) AS "Critical"
    FROM "ReportReviewWarnings"
    WHERE "CompanyId" = ? AND "ImportDate" = ? AND "Confirm" = TRUE
  ) as "Confirm",
  (
    SELECT count(*) as "Warning"
    FROM "ReportReviewWarnings"
    WHERE "CompanyId" = ? AND "ImportDate" = ? AND "Confirm" <> TRUE
  ) as "Notice"