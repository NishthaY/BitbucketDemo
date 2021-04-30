\set db advice2pay


-- Rename the 'Personal SSN' column to 'Person SSN'
update "MappingColumns" set "Display" = 'Person SSN' where "Name" = 'ssn';
update "CompanyMappingColumn" set "Display" = 'Person SSN' where "Name" = 'ssn';

