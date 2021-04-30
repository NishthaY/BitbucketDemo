\set db advice2pay

-- Add two new columsn to the ProcessQueue table.  This will allow us
-- to collect records by user and company.
ALTER TABLE "ProcessQueue" ADD "CompanyId" INT NULL;
ALTER TABLE "ProcessQueue" ADD "UserId" INT NULL;