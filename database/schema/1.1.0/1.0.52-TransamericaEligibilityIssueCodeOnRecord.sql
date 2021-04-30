\set db advice2pay

-- Create a new record on the eligibility table that allows us to mark mark lives
-- as having issues.
ALTER TABLE "ReportTransamericaEligibility" ADD "IssueCode" TEXT NULL;
