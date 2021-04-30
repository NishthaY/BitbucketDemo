\set db advice2pay


-- Adding some extra fields so I can use this temp space to not only hold
-- keys but other data points too when needed.
ALTER TABLE "CompanyCommissionWorker" ADD "CommissionEffectiveDate" DATE NULL;
ALTER TABLE "CompanyCommissionWorker" ADD "Before-CommissionEffectiveDate" DATE NULL;
ALTER TABLE "CompanyCommissionWorker" ADD "MonthlyCost" NUMERIC(8,4) NULL;