\set db advice2pay

--LifeData: ImportDataId
CREATE INDEX lifedata_importdataid_idx ON "LifeData" ("ImportDataId");

--WashedData: ImportDataId
CREATE INDEX washeddata_importdataid_index ON "WashedData" ("ImportDataId");

--CompanyCarrier: CompanyId,CarrierNormalized
CREATE INDEX companycarrier_normalized_index ON "CompanyCarrier" ("CompanyId", "CarrierNormalized");

--CompanyPlanType: CompanyId,CarrierId,PlanTypeNormalized
CREATE INDEX companyplantype_normalized_index ON "CompanyPlanType" ("CompanyId", "CarrierId", "PlanTypeNormalized");

--CompanyPlan: CompanyId,CarrierId,PlanTypeId,PlanNormalized
CREATE INDEX companyplan_normalized_index ON "CompanyPlan" ("CompanyId", "CarrierId", "PlanTypeId", "PlanNormalized");

--CompanyCoverageTier: CompanyId,CarrierId,PlanTypeId,PlanId,CoverageTierNormalized
CREATE INDEX companycoveragetier_normalized_index ON "CompanyCoverageTier" ("CompanyId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierNormalized");

-- Update the report names to match the latest feedback.
update "ReportType" set "Display" = 'Billing Detail' where "Name" = 'detail';
update "ReportType" set "Display" = 'Billing Premium Equivalent Detail' where "Name" = 'pe_detail';
update "ReportType" set "Display" = 'Transamerica Eligibility' where "Name" = 'eligibility';
update "ReportType" set "Display" = 'Billing Summary' where "Name" = 'summary';
update "ReportType" set "Display" = 'Billing Premium Equivalent Summary' where "Name" = 'pe_summary';

