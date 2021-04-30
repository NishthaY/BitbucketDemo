\set db advice2pay

-- Keep these columns encrypted in the database.
update "MappingColumns" set "Encrypted" = true where "Name" = 'middle_name';
update "MappingColumns" set "Encrypted" = true where "Name" = 'suffix';
update "MappingColumns" set "Encrypted" = true where "Name" = 'postalcode';
