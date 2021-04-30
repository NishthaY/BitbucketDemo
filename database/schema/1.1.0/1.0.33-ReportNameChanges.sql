\set db advice2pay

update "ReportType" set "Display" = 'Transamerica Commissions Import File' where "Name" = 'transamerica_commission';
update "ReportType" set "Display" = 'Transamerica Actuarial Import File' where "Name" = 'transamerica_actuarial';