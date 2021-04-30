\set db advice2pay

-- Add a Promote column which tells us if the company does not
-- have this report, show it to them anyway.
ALTER TABLE "ReportType" ADD "Promote" BOOLEAN DEFAULT false;
update "ReportType" set "Promote" = true where "Name" in ( 'detail', 'summary', 'eligibility');


-- Update the report names to match the latest feedback.
update "ReportType" set "Display" = 'Billing Detail Report' where "Name" = 'detail';
update "ReportType" set "Display" = 'Billing Detail Premium Equivalent Report' where "Name" = 'pe_detail';
update "ReportType" set "Display" = 'Transamerica Eligibility Import File' where "Name" = 'eligibility';
update "ReportType" set "Display" = 'Billing Summary Report' where "Name" = 'summary';
update "ReportType" set "Display" = 'Billing Summary Premium Equivalent Report' where "Name" = 'pe_summary';

-- Set the promote flags so we know which ones to nag about on the download screen
update "ReportType" set "Promote" = false where "Name" = 'detail';
update "ReportType" set "Promote" = false where "Name" = 'pe_detail';
update "ReportType" set "Promote" = true where "Name" = 'eligibility';
update "ReportType" set "Promote" = false where "Name" = 'summary';
update "ReportType" set "Promote" = false where "Name" = 'pe_summary';